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
use Hyperf\Coordinator\Timer;
use Hyperf\Engine\Channel;
use Hyperf\Pool\Exception\InvalidArgumentException;
use Hyperf\Pool\Exception\SocketPopException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class KeepaliveConnection implements ConnectionInterface
{
    protected Timer $timer;

    protected Channel $channel;

    protected float $lastUseTime = 0.0;

    protected ?int $timerId = null;

    protected bool $connected = false;

    protected string $name = 'keepalive.connection';

    public function __construct(protected ContainerInterface $container, protected Pool $pool)
    {
        $this->timer = new Timer();
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

        $channel = new Channel(1);
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
            && $this->channel->getLength() > 0;
    }

    protected function addHeartbeat()
    {
        $this->connected = true;
        $this->timerId = $this->timer->tick($this->getHeartbeatSeconds(), function () {
            try {
                if (! $this->isConnected()) {
                    return;
                }

                if ($this->isTimeout()) {
                    // The socket does not use in double of heartbeat.
                    $this->close();
                    return;
                }

                $this->heartbeat();
            } catch (Throwable $throwable) {
                $this->clear();
                if ($logger = $this->getLogger()) {
                    $message = sprintf('Socket of %s heartbeat failed, %s', $this->name, $throwable);
                    $logger->error($message);
                }
            }
        });
    }

    /**
     * @return int seconds
     */
    protected function getHeartbeatSeconds(): int
    {
        $heartbeat = $this->pool->getOption()->getHeartbeat();

        if ($heartbeat > 0) {
            return intval($heartbeat);
        }

        return 10;
    }

    protected function clear()
    {
        $this->connected = false;
        if ($this->timerId) {
            $this->timer->clear($this->timerId);
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
