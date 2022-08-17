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
namespace Hyperf\Redis;

use Hyperf\Redis\Pool\PoolFactory;

/**
 * @mixin \Redis
 */
class RedisProxy extends Redis
{
    public function __construct(PoolFactory $factory, string $pool)
    {
        parent::__construct($factory);

        $this->poolName = $pool;
    }

    /**
     * WARN: Can't remove this function, because AOP need it.
     * @see https://github.com/hyperf/hyperf/issues/1239
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        return parent::__call($name, $arguments);
    }
}
