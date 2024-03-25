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

use Hyperf\Cache\Collector\CoroutineMemory;
use Hyperf\Cache\Collector\CoroutineMemoryKey;

class CoroutineMemoryDriver extends Driver implements KeyCollectorInterface
{
    public function get($key, $default = null): mixed
    {
        return $this->getCollection()->get($key, $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->getCollection()->offsetSet($key, $value);
        return true;
    }

    public function delete($key): bool
    {
        $this->getCollection()->offsetUnset($key);
        return true;
    }

    public function clear(): bool
    {
        $this->getCollection()->clear();
        return true;
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
        foreach ($values as $key => $value) {
            $this->set($key, $values, $ttl);
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

    public function has($key): bool
    {
        return $this->getCollection()->has($key);
    }

    public function fetch(string $key, $default = null): array
    {
        if (! $this->has($key)) {
            return [false, $default];
        }

        return [true, $this->get($key)];
    }

    public function clearPrefix(string $prefix): bool
    {
        return $this->getCollection()->clearPrefix($prefix);
    }

    public function addKey(string $collector, string $key): bool
    {
        $instance = CoroutineMemoryKey::instance();
        $data = $instance->get($collector, []);
        $data[] = $key;
        $instance->put($collector, $data);

        return true;
    }

    public function keys(string $collector): array
    {
        return CoroutineMemoryKey::instance()->get($collector, []);
    }

    public function delKey(string $collector, string ...$key): bool
    {
        $instance = CoroutineMemoryKey::instance();
        $result = [];
        $data = $instance->get($collector, []);
        foreach ($data as $item) {
            if (! in_array($item, $key)) {
                $result[] = $item;
            }
        }
        $instance->put($collector, $result);
        return true;
    }

    protected function getCollection()
    {
        return CoroutineMemory::instance();
    }
}
