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

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\PoolInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Event\ReleaseConnection;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

abstract class Connection implements ConnectionInterface
{
    protected float $lastUseTime = 0.0;

    protected float $lastReleaseTime = 0.0;

    private ?EventDispatcherInterface $dispatcher = null;

    private ?StdoutLoggerInterface $logger = null;

    public function __construct(protected ContainerInterface $container, protected PoolInterface $pool)
    {
        if ($this->container->has(EventDispatcherInterface::class)) {
            $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
        }

        if ($this->container->has(StdoutLoggerInterface::class)) {
            $this->logger = $this->container->get(StdoutLoggerInterface::class);
        }
    }

    public function release(): void
    {
        try {
            $this->lastReleaseTime = microtime(true);
            $events = $this->pool->getOption()->getEvents();
            if (in_array(ReleaseConnection::class, $events, true)) {
                $this->dispatcher?->dispatch(new ReleaseConnection($this));
            }
        } catch (Throwable $exception) {
            $this->logger?->error((string) $exception);
        } finally {
            $this->pool->release($this);
        }
    }

    public function getConnection()
    {
        try {
            return $this->getActiveConnection();
        } catch (Throwable $exception) {
            $this->logger?->warning('Get connection failed, try again. ' . $exception);
            return $this->getActiveConnection();
        }
    }

    public function check(): bool
    {
        $maxIdleTime = $this->pool->getOption()->getMaxIdleTime();
        $now = microtime(true);
        if ($now > $maxIdleTime + $this->lastUseTime) {
            return false;
        }

        $this->lastUseTime = $now;
        return true;
    }

    public function getLastUseTime(): float
    {
        return $this->lastUseTime;
    }

    public function getLastReleaseTime(): float
    {
        return $this->lastReleaseTime;
    }

    abstract public function getActiveConnection();
}
