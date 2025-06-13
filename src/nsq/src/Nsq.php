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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Socket;
use Hyperf\Nsq\Exception\SocketSendException;
use Hyperf\Nsq\Pool\NsqConnection;
use Hyperf\Nsq\Pool\NsqPoolFactory;
use Hyperf\Pool\Exception\ConnectionException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Nsq
{
    protected ?Socket $socket = null;

    protected Pool\NsqPool $pool;

    protected MessageBuilder $builder;

    protected LoggerInterface $logger;

    protected bool $subscribing = true;

    protected bool $listen = false;

    public function __construct(protected ContainerInterface $container, string $pool = 'default')
    {
        $this->pool = $container->get(NsqPoolFactory::class)->getPool($pool);
        $this->builder = $container->get(MessageBuilder::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * @param string|string[] $message
     */
    public function publish(string $topic, array|string $message, float $deferTime = 0.0, bool $confirm = false): bool
    {
        if (is_array($message)) {
            if ($deferTime > 0) {
                $isOk = true;
                foreach ($message as $value) {
                    if (! $this->sendDPub($topic, $value, $deferTime, $confirm)) {
                        $isOk = false;
                    }
                }
                return $isOk;
            }

            return $this->sendMPub($topic, $message, $confirm);
        }

        if ($deferTime > 0) {
            return $this->sendDPub($topic, $message, $deferTime, $confirm);
        }

        return $this->sendPub($topic, $message, $confirm);
    }

    public function subscribe(string $topic, string $channel, callable $callback, bool $autoStop = false): void
    {
        if (! $this->listen && $autoStop) {
            $this->listen = true;
            Coroutine::create(function () {
                while (true) {
                    if (! $this->subscribing || CoordinatorManager::until(Constants::WORKER_EXIT)->yield(5)) {
                        $this->stopSubscribe();
                        break;
                    }
                }
            });
        }

        $this->call(function (Socket $socket) use ($topic, $channel, $callback) {
            $this->sendSub($socket, $topic, $channel);
            while ($this->subscribing && $this->sendRdy($socket)) {
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
                        } catch (Throwable $throwable) {
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

    public function stopSubscribe(): void
    {
        $this->subscribing = false;
    }

    protected function sendMPub(string $topic, array $messages, bool $confirm = false): bool
    {
        $payload = $this->builder->buildMPub($topic, $messages);
        return $this->call(function (Socket $socket) use ($payload, $confirm) {
            if ($socket->sendAll($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }

            if ($confirm) {
                $subscriber = new Subscriber($socket);
                $subscriber->recv();
                return $subscriber->isOk();
            }
            return true;
        });
    }

    protected function sendPub(string $topic, string $message, bool $confirm = false): bool
    {
        $payload = $this->builder->buildPub($topic, $message);
        return $this->call(function (Socket $socket) use ($payload, $confirm) {
            if ($socket->sendAll($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }

            if ($confirm) {
                $subscriber = new Subscriber($socket);
                $subscriber->recv();
                return $subscriber->isOk();
            }

            return true;
        });
    }

    protected function sendDPub(string $topic, string $message, float $deferTime = 0.0, bool $confirm = false): bool
    {
        $payload = $this->builder->buildDPub($topic, $message, intval($deferTime * 1000));
        return $this->call(function (Socket $socket) use ($payload, $confirm) {
            if ($socket->sendAll($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }

            if ($confirm) {
                $subscriber = new Subscriber($socket);
                $subscriber->recv();
                return $subscriber->isOk();
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
        } catch (Throwable $throwable) {
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

        $reader = new Subscriber($socket);
        if (! $reader->recv()->isOk()) {
            throw new SocketSendException('SUB send failed, ' . $reader->getPayload());
        }
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
