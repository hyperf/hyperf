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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Exception\SocketPopException;
use Hyperf\Utils\ApplicationContext;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine;
use Swoole\Timer;

abstract class Socket implements SocketInterface
{
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
    protected $name;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var float
     */
    protected $timeout;

    /**
     * @var float
     */
    protected $heartbeat;

    public function __construct(string $name, string $host, int $port, float $timeout, float $heartbeat, bool $connect = true)
    {
        $this->name = $name;
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->heartbeat = $heartbeat;

        if ($connect) {
            $this->reconnect();
        }
    }

    public function __destruct()
    {
        $this->clear();
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

    public function call(Closure $closure, bool $using = true)
    {
        if (! $this->isConnected()) {
            $this->reconnect();
        }

        $client = $this->channel->pop($this->timeout);
        if ($client === false) {
            throw new SocketPopException(sprintf('Socket of %s is exhausted. Cannot establish socket before timeout.', $this->name));
        }

        try {
            $result = $closure($client);
            if ($using) {
                $this->lastUseTime = microtime(true);
            }
        } catch (\Throwable $throwable) {
            $this->clear();
            throw $throwable;
        } finally {
            $this->channel->push($client, 0.001);
        }

        return $result;
    }

    public function heartbeat(): void
    {
        $this->call(function ($socket) {
            $this->sendHeartbeat($socket);
        }, false);
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
                    $socket->close();
                });
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

                if ($this->lastUseTime < microtime(true) - $this->heartbeat * 2) {
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

    /**
     * Connect and return the active socket.
     * @return mixed
     */
    abstract protected function connect();

    abstract protected function sendHeartbeat($socket);
}
