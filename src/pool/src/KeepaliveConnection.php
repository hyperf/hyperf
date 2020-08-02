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
namespace Hyperf\Pool;

use Closure;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Exception\InvalidArgumentException;
use Hyperf\Pool\Exception\SocketPopException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine;
use Swoole\Timer;

abstract class KeepaliveConnection implements ConnectionInterface
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

    /**
     * @var string
     */
    protected $name = 'keepalive.connection';

    public function __construct(ContainerInterface $container, Pool $pool)
    {
        $this->container = $container;
        $this->pool = $pool;
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function release(): void
    {
        $this->pool->release($this);
    }

    public function getConnection()
    {
        throw new InvalidArgumentException('Please use call instead of getConnection.');
    }

    public function check(): bool
    {
        return $this->isConnected();
    }

    public function reconnect(): bool
    {
        $this->close();

        $connection = $this->getActiveConnection();

        $channel = new Coroutine\Channel(1);
        $channel->push($connection);
        $this->channel = $channel;
        $this->lastUseTime = microtime(true);

        $this->addHeartbeat();

        return true;
    }

    /**
     * @param bool $refresh refresh last use time or not
     * @return mixed
     */
    public function call(Closure $closure, bool $refresh = true)
    {
        if (! $this->isConnected()) {
            $this->reconnect();
        }

        $connection = $this->channel->pop($this->pool->getOption()->getWaitTimeout());
        if ($connection === false) {
            throw new SocketPopException(sprintf('Socket of %s is exhausted. Cannot establish socket before timeout.', $this->name));
        }

        try {
            $result = $closure($connection);
            if ($refresh) {
                $this->lastUseTime = microtime(true);
            }
        } finally {
            if ($this->isConnected()) {
                $this->channel->push($connection, 0.001);
            } else {
                // Unset and drop the connection.
                unset($connection);
            }
        }

        return $result;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function close(): bool
    {
        if ($this->isConnected()) {
            $this->call(function ($connection) {
                try {
                    if ($this->isConnected()) {
                        $this->sendClose($connection);
                    }
                } finally {
                    $this->clear();
                }
            }, false);
        }

        return true;
    }

    public function isTimeout(): bool
    {
        return $this->lastUseTime < microtime(true) - $this->pool->getOption()->getMaxIdleTime()
            && $this->channel->length() > 0;
    }

    protected function addHeartbeat()
    {
        $this->connected = true;
        $this->timerId = Timer::tick($this->getHeartbeat(), function () {
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

    /**
     * @return int ms
     */
    protected function getHeartbeat(): int
    {
        $heartbeat = $this->pool->getOption()->getHeartbeat();

        if ($heartbeat > 0) {
            return intval($heartbeat * 1000);
        }

        return 10 * 1000;
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
        if ($this->container->has(StdoutLoggerInterface::class)) {
            return $this->container->get(StdoutLoggerInterface::class);
        }

        return null;
    }

    protected function heartbeat(): void
    {
    }

    /**
     * Send close protocol.
     * @param mixed $connection
     */
    protected function sendClose($connection): void
    {
    }

    /**
     * Connect and return the active connection.
     * @return mixed
     */
    abstract protected function getActiveConnection();
}
