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

namespace Hyperf\Pool;

use Closure;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Exception\InvalidArgumentException;
use Hyperf\Pool\Exception\SocketPopException;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine;
use Swoole\Timer;

abstract class HeartbeatConnection implements ConnectionInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var Coroutine\Channel
     */
    protected $channel;

    /**
     * @var float
     */
    protected $lastUseTime = 0.0;

    /**
     * @var null|int
     */
    protected $timerId;

    /**
     * @var bool
     */
    protected $connected = false;

    public function __construct(ContainerInterface $container, Pool $pool)
    {
        $this->container = $container;
        $this->pool = $pool;
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function getConnection()
    {
        throw new InvalidArgumentException('Please use call instead of getConnection.');
    }

    public function check(): bool
    {
        return $this->isConnected();
    }

    public function reconnect(): void
    {
        $this->close();

        $socket = $this->connect();

        $channel = new Coroutine\Channel(1);
        $channel->push($socket);
        $this->channel = $channel;
        $this->connected = true;
        $this->lastUseTime = microtime(true);

        $this->addHeartbeat();
    }

    /**
     * @return mixed
     */
    public function call(Closure $closure, bool $isUserCall = true)
    {
        if (! $this->isConnected()) {
            $this->reconnect();
        }

        $socket = $this->channel->pop($this->timeout);
        if ($socket === false) {
            throw new SocketPopException(sprintf('Socket of %s is exhausted. Cannot establish socket before timeout.', $this->name));
        }

        try {
            $result = $closure($socket);
            if ($isUserCall) {
                $this->lastUseTime = microtime(true);
            }
        } catch (\Throwable $throwable) {
            $this->clear();
            throw $throwable;
        } finally {
            if ($this->isConnected()) {
                $this->channel->push($socket, 0.001);
            } else {
                $this->sendClose($socket);
            }
        }

        return $result;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function close(): void
    {
        try {
            if ($this->isConnected()) {
                $this->call(function ($socket) {
                    if ($this->isConnected()) {
                        $this->sendClose($socket);
                    }
                }, false);
            }
        } finally {
            $this->clear();
        }
    }

    protected function addHeartbeat()
    {
        $this->clear();
        $this->timerId = Timer::tick($this->heartbeat * 1000, function () {
            try {
                if (! $this->isConnected()) {
                    return;
                }

                if ($this->isTimeout()) {
                    // The socket does not used in double of heartbeat.
                    $this->close();
                    return;
                }

                $this->heartbeat();
            } catch (\Throwable $throwable) {
                $this->clear();
                if ($logger = $this->getLogger()) {
                    $message = sprintf('Socket of %s heartbeat failed, %s', $this->name, (string) $throwable);
                    $logger->error($message);
                }
            }
        });
    }

    protected function isTimeout(): bool
    {
        return $this->lastUseTime < microtime(true) - $this->heartbeat * 2;
    }

    protected function clear()
    {
        $this->connected = false;
        if ($this->timerId) {
            Timer::clear($this->timerId);
            $this->timerId = null;
        }
    }

    protected function getLogger(): ?LoggerInterface
    {
        if (ApplicationContext::hasContainer() && $container = ApplicationContext::getContainer()) {
            if ($container->has(StdoutLoggerInterface::class)) {
                return $container->get(StdoutLoggerInterface::class);
            }
        }

        return null;
    }

    protected function heartbeat()
    {
    }

    /**
     * Connect and return the active socket.
     * @return mixed
     */
    abstract protected function connect();

    /**
     * Send close protocol.
     * @param $socket
     */
    abstract protected function sendClose($socket): void;
}
