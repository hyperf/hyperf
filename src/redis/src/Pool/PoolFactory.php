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

namespace Hyperf\Redis\Pool;

use Hyperf\Di\Container;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Channel;

class PoolFactory
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Channel[]
     */
    protected $pools = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPool(string $name): RedisPool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        return $this->pools[$name] = $this->container->make(RedisPool::class, ['name' => $name]);
    }
}
