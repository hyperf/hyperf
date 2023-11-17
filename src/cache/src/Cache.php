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

use Hyperf\Cache\Driver\DriverInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @method array{bool, mixed} fetch($key = null, $default = null)
 * @method mixed getConnection()
 * @method bool clearPrefix(string $prefix)
 */
class Cache implements CacheInterface
{
    public function __construct(protected DriverInterface $driver)
    {
    }

    public function __call($name, $arguments)
    {
        return $this->driver->{$name}(...$arguments);
    }

    public function get($key, $default = null): mixed
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function delete($key): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function clear(): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function deleteMultiple($keys): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function has($key): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
