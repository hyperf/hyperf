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

namespace Hyperf\Cache\Driver;

use Hyperf\Collection\Arr;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class CoRedisDriver extends Driver implements KeyCollectorInterface
{
    protected RedisDriver $redisDriver;

    protected CoroutineMemoryDriver $coroutineMemoryDriver;

    public function __construct(ContainerInterface $container, array $config)
    {
        $co = Arr::get($config, 'co', $config);
        $redis = Arr::get($config, 'redis', $config);
        $this->redisDriver = make(RedisDriver::class, ['config' => $redis]);
        $this->coroutineMemoryDriver = make(CoroutineMemoryDriver::class, ['config' => $co]);
        parent::__construct($container, $config);
    }

    public function get($key, $default = null): mixed
    {
        $co = $this->coroutineMemoryDriver->get($key, $default);
        if ($co === $default) {
            return $this->redisDriver->get($key, $default);
        }
        return $co;
    }

    public function fetch(string $key, $default = null): array
    {
        [$coBool, $coData] = $coResult = $this->coroutineMemoryDriver->fetch($key, $default);
        if ($coBool) {
            return $coResult;
        }

        return $this->redisDriver->fetch($key, $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->coroutineMemoryDriver->set($key, $value, $ttl);
        return $this->redisDriver->set($key, $value, $ttl);
    }

    public function delete($key): bool
    {
        $this->coroutineMemoryDriver->delete($key);
        return $this->redisDriver->delete($key);
    }

    public function clear(): bool
    {
        return $this->clearPrefix('');
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->redisDriver->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return $this->redisDriver->setMultiple($values, $values);
    }

    public function deleteMultiple($keys): bool
    {
        $this->coroutineMemoryDriver->deleteMultiple($keys);
        return $this->redisDriver->deleteMultiple($keys);
    }

    public function has($key): bool
    {
        return $this->coroutineMemoryDriver->has($key) || $this->redisDriver->has($key);
    }

    public function clearPrefix(string $prefix): bool
    {
        $this->coroutineMemoryDriver->clearPrefix($prefix);
        return $this->redisDriver->clearPrefix($prefix);
    }

    public function addKey(string $collector, string $key): bool
    {
        $this->coroutineMemoryDriver->addKey($collector, $key);
        return $this->redisDriver->addKey($collector, $key);
    }

    public function keys(string $collector): array
    {
        return $this->redisDriver->keys($collector);
    }

    public function delKey(string $collector, string ...$key): bool
    {
        $this->coroutineMemoryDriver->delKey($collector, ...$key);
        return $this->redisDriver->delKey($collector, ...$key);
    }

    public function getConnection(): mixed
    {
        return $this->redisDriver->getConnection();
    }
}
