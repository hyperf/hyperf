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

namespace Hyperf\Cache\Driver;

use Hyperf\Cache\Exception\CacheException;
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
        $res = $this->redis->get($key);
        if ($res === false) {
            return $default;
        }

        return $this->packer->unpack($res);
    }

    public function fetch(string $key, $default = null): array
    {
        $res = $this->redis->get($key);
        if ($res === false) {
            return [false, $default];
        }

        return [true, $this->packer->unpack($res)];
    }

    public function set($key, $value, $ttl = null)
    {
        $res = $this->packer->pack($value);
        if ($ttl > 0) {
            return $this->redis->set($key, $res, $ttl);
        }

        return $this->redis->set($key, $res);
    }

    public function delete($key)
    {
        return $this->redis->delete($key);
    }

    public function clear()
    {
        throw new CacheException('The method is not invalid!');
    }

    public function getMultiple($keys, $default = null)
    {
        throw new CacheException('The method is not invalid!');
    }

    public function setMultiple($values, $ttl = null)
    {
        throw new CacheException('The method is not invalid!');
    }

    public function deleteMultiple($keys)
    {
        throw new CacheException('The method is not invalid!');
    }

    public function has($key)
    {
        return (bool) $this->redis->exists($key);
    }
}
