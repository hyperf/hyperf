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
use Hyperf\Cache\Collector\Memory;
use Hyperf\Cache\Exception\InvalidArgumentException;
use Hyperf\Cache\Exception\OverflowException;
use Psr\Container\ContainerInterface;

class MemoryDriver extends Driver implements DriverInterface
{
    protected ?int $size = null;

    protected bool $throwWhenSizeExceeded = false;

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);

        if (isset($config['size'])) {
            $this->size = (int) $config['size'];
        }
        if (isset($config['throw_when_size_exceeded'])) {
            $this->throwWhenSizeExceeded = (bool) $config['throw_when_size_exceeded'];
        }
    }

    public function fetch($key, $default = null): array
    {
        return [
            $this->has($key),
            $this->get($key, $default),
        ];
    }

    public function has($key): bool
    {
        return $this->getCollector()->has($this->getCacheKey($key));
    }

    public function get($key, $default = null): mixed
    {
        return $this->getCollector()->get(
            $this->getCacheKey($key),
            $default
        );
    }

    public function set($key, $value, $ttl = null): bool
    {
        if (
            $this->size > 0
            && $this->getCollector()->size() >= $this->size
        ) {
            if ($this->throwWhenSizeExceeded) {
                throw new OverflowException('The memory cache is full!');
            }
            return false;
        }

        $seconds = $this->secondsUntil($ttl);
        return $this->getCollector()->set(
            $this->getCacheKey($key),
            $value,
            $seconds <= 0 ? null : Carbon::now()->addSeconds($seconds)
        );
    }

    public function delete($key): bool
    {
        return $this->getCollector()->delete($this->getCacheKey($key));
    }

    public function clear(): bool
    {
        return $this->getCollector()->clear();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null): bool
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
        return $this->getCollector()->clearPrefix($this->getCacheKey($prefix));
    }

    public function getConnection(): mixed
    {
        return $this;
    }

    protected function getCollector(): Memory
    {
        return Memory::instance();
    }
}
