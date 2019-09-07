<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DistributedLocks\Driver;

use Psr\Container\ContainerInterface;

class RedisDriver extends Driver
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);

        $this->redis = $container->get(\Redis::class);
    }

    public function get($key, $default = null)
    {
        $res = $this->redis->get($this->getMutexKey($key));
        if ($res === false) {
            return $default;
        }

        return $this->packer->unpack($res);
    }

    public function fetch(string $key, $default = null): array
    {
        $res = $this->redis->get($this->getMutexKey($key));
        if ($res === false) {
            return [false, $default];
        }

        return [true, $this->packer->unpack($res)];
    }
    
    public function lock()
    {
        // TODO: Implement lock() method.
    }

    public function unlock()
    {
        // TODO: Implement unlock() method.
    }
}
