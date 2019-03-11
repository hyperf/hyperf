<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Pool;

use Psr\Container\ContainerInterface;
use Hyperf\Contract\ConnectionInterface;

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
     * @var bool
     */
    protected $release = false;

    public function __construct(ContainerInterface $container, Pool $pool)
    {
        $this->container = $container;
        $this->pool = $pool;
    }

    public function release(): void
    {
        if (! $this->release) {
            $this->release = true;
            $this->pool->release($this);
        }
    }

    public function getConnection()
    {
        $this->release = false;
        return $this->getActiveConnection();
    }

    abstract public function getActiveConnection();
}
