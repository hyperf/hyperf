<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
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
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * @param string|string[] $message
     * @throws \Throwable
     */
    public function publish(string $topic, $message, float $deferTime = 0.0): bool
    {
        if (is_array($message)) {
            if ($deferTime > 0) {
                foreach ($message as $value) {
                    $this->sendDPub($topic, $value, $deferTime);
                }
                return true;
            }

            return $this->sendMPub($topic, $message);
        }

        if ($deferTime > 0) {
            return $this->sendDPub($topic, $message, $deferTime);
        }

        return $this->sendPub($topic, $message);
    }

    public function subscribe(string $topic, string $channel, callable $callback): void
    {
        $this->call(function (Socket $socket) use ($topic, $channel,$callback) {
            $this->sendSub($socket, $topic, $channel);
            while ($this->sendRdy($socket)) {
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
                            continue;
                        }

                        $socket->sendAll($this->builder->buildFin($message->getMessageId()));
                    }
                }
            }
        });
    }

    protected function sendMPub(string $topic, array $messages): bool
    {
        $payload = $this->builder->buildMPub($topic, $messages);
        return $this->call(function (Socket $socket) use ($payload) {
            if ($socket->send($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }
            return true;
        });
    }

    protected function sendPub(string $topic, string $message): bool
    {
        $payload = $this->builder->buildPub($topic, $message);
        return $this->call(function (Socket $socket) use ($payload) {
            if ($socket->send($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }
            return true;
        });
    }

    protected function sendDPub(string $topic, string $message, float $deferTime = 0.0): bool
    {
        $payload = $this->builder->buildDPub($topic, $message, intval($deferTime * 1000));
        return $this->call(function (Socket $socket) use ($payload) {
            if ($socket->send($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }
            return true;
        });
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

    protected function sendSub(Socket $socket, string $topic, string $channel): void
    {
        $result = $socket->sendAll($this->builder->buildSub($topic, $channel));
        if ($result === false) {
            throw new SocketSendException('SUB send failed, the errorCode is ' . $socket->errCode);
        }
        $socket->recv();
    }

    protected function sendRdy(Socket $socket)
    {
        $result = $socket->sendAll($this->builder->buildRdy(1));
        if ($result === false) {
            throw new SocketSendException('RDY send failed, the errorCode is ' . $socket->errCode);
        }

        return $result;
    }
}
