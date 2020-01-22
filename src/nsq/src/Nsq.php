<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Nsq;

use Closure;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nsq\Exception\SocketSendException;
use Hyperf\Nsq\Pool\NsqConnection;
use Hyperf\Nsq\Pool\NsqPoolFactory;
use Hyperf\Pool\Exception\ConnectionException;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Socket;

class Nsq
{
    /**
     * @var \Swoole\Coroutine\Socket
     */
    protected $socket;

    /**
     * @var Packer
     */
    protected $packer;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Pool\NsqPool
     */
    protected $pool;

    /**
     * @var MessageBuilder
     */
    protected $builder;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container, string $pool = 'default')
    {
        $this->container = $container;
        $this->pool = $container->get(NsqPoolFactory::class)->getPool($pool);
        $this->builder = $container->get(MessageBuilder::class);
        $this->packer = $container->get(Packer::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function publish(string $topic, string $message): bool
    {
        $payload = $this->builder->buildPub($topic, $message);
        return $this->call(function (Socket $socket) use ($payload) {
            if ($socket->send($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }
            return true;
        });
    }

    public function subscribe(string $topic, string $channel, callable $callback): void
    {
        $this->sendSub($topic, $channel);
        $this->sendRdy();

        while ($this->sendRdy()) {
            $this->call(function (Socket $socket) use ($callback) {
                $reader = new Subscriber($socket);
                $reader->recv();

                if ($reader->isMessage()) {
                    if ($reader->isHeartbeat()) {
                        $socket->sendAll($this->builder->buildNop());
                    } else {
                        $message = $reader->getMessage();
                        $result = null;
                        try {
                            $result = $callback($message);
                        } catch (\Throwable $throwable) {
                            $result = Result::DROP;
                            $this->logger->error('Subscribe failed, ' . (string) $throwable);
                        }

                        if ($result === Result::REQUEUE) {
                            $socket->sendAll($this->builder->buildTouch($message->getMessageId()));
                            $socket->sendAll($this->builder->buildReq($message->getMessageId()));
                            return;
                        }

                        $socket->sendAll($this->builder->buildFin($message->getMessageId()));
                    }
                }
            });
        }
    }

    protected function call(Closure $closure)
    {
        /** @var NsqConnection $connection */
        $connection = $this->pool->get();
        try {
            return $connection->call($closure);
        } catch (\Throwable $throwable) {
            $connection->close();
            throw $throwable;
        } finally {
            $connection->release();
        }
    }

    protected function sendSub(string $topic, string $channel): void
    {
        $this->call(function (Socket $socket) use ($topic, $channel) {
            $result = $socket->sendAll($this->builder->buildSub($topic, $channel));
            if ($result === false) {
                throw new SocketSendException('SUB send failed, the errorCode is ' . $socket->errCode);
            }
            $socket->recv();
        });
    }

    protected function sendRdy()
    {
        return $this->call(function (Socket $socket) {
            $result = $socket->sendAll($this->builder->buildRdy(1));
            if ($result === false) {
                throw new SocketSendException('RDY send failed, the errorCode is ' . $socket->errCode);
            }

            return $result;
        });
    }
}
