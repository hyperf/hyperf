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
namespace Hyperf\Cache;

use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface
{
    protected $driver;

    public function __construct(CacheManager $manager)
    {
        $this->driver = $manager->getDriver();
    }

    public function __call($name, $arguments)
    {
        return $this->driver->{$name}(...$arguments);
    }

    public function get($key, $default = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function delete($key)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function clear()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getMultiple($keys, $default = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function setMultiple($values, $ttl = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function deleteMultiple($keys)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function has($key)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
