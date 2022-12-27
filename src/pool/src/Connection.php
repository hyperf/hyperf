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
use Psr\Container\ContainerInterface;
use Throwable;

abstract class Connection implements ConnectionInterface
{
    protected float $lastUseTime = 0.0;

    public function __construct(protected ContainerInterface $container, protected PoolInterface $pool)
    {
    }

    public function release(): void
    {
        $this->pool->release($this);
    }

    public function getConnection()
    {
        try {
            return $this->getActiveConnection();
        } catch (Throwable $exception) {
            if ($this->container->has(StdoutLoggerInterface::class) && $logger = $this->container->get(StdoutLoggerInterface::class)) {
                $logger->warning('Get connection failed, try again. ' . $exception);
            }
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

    abstract public function getActiveConnection();
}
