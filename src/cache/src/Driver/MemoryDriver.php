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

use Carbon\Carbon;
use DateInterval;
use Hyperf\Cache\Collector\MemoryStorage;
use Hyperf\Cache\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;

class MemoryDriver extends Driver implements DriverInterface
{
    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);
    }

    /**
     * @return array<bool, mixed>
     */
    public function fetch(string $key, mixed $default = null): array
    {
        return [
            $this->has($key),
            $this->get($key, $default),
        ];
    }

    public function has(string $key): bool
    {
        return $this->getStorage()->has($this->getCacheKey($key));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getStorage()->get(
            $this->getCacheKey($key),
            $default
        );
    }

    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        $seconds = $this->secondsUntil($ttl);
        return $this->getStorage()->set(
            $this->getCacheKey($key),
            $value,
            $ttl <= 0 ? null : Carbon::now()->addSeconds($seconds)
        );
    }

    public function delete(string $key): bool
    {
        return $this->getStorage()->delete($this->getCacheKey($key));
    }

    public function clear(): bool
    {
        return $this->getStorage()->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        if (! is_array($values)) {
            throw new InvalidArgumentException('The values is invalid!');
        }

        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function clearPrefix(string $prefix): bool
    {
        return $this->getStorage()->clearPrefix($this->getCacheKey($prefix));
    }

    public function getConnection(): mixed
    {
        return $this;
    }

    protected function getStorage(): MemoryStorage
    {
        return MemoryStorage::instance();
    }
}
