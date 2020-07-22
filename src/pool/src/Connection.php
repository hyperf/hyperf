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
use Psr\Container\ContainerInterface;

abstract class Connection implements ConnectionInterface
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
     * @var float
     */
    protected $lastUseTime = 0.0;

    public function __construct(ContainerInterface $container, Pool $pool)
    {
        $this->container = $container;
        $this->pool = $pool;
    }

    public function release(): void
    {
        $this->pool->release($this);
    }

    public function getConnection()
    {
        return $this->getActiveConnection();
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
