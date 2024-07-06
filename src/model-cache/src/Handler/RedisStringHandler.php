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

namespace Hyperf\ModelCache\Handler;

use Hyperf\Codec\Packer\PhpSerializerPacker;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\PackerInterface;
use Hyperf\ModelCache\Config;
use Hyperf\ModelCache\Exception\CacheException;
use Hyperf\Redis\RedisProxy;
use Hyperf\Support\Traits\InteractsWithTime;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class RedisStringHandler implements HandlerInterface
{
    use InteractsWithTime;

    protected RedisProxy $redis;

    protected PackerInterface $packer;

    public function __construct(protected ContainerInterface $container, protected Config $config)
    {
        if (! $container->has(RedisProxy::class)) {
            throw new CacheException(sprintf('Entry[%s] of the container is not exist.', RedisProxy::class));
        }

        $this->redis = make(RedisProxy::class, ['pool' => $config->getPool()]);
        $this->packer = $container->get(PhpSerializerPacker::class);
    }

    public function get($key, $default = null): mixed
    {
        $data = $this->redis->get($key);
        if (! $data) {
            return $default;
        }

        return $this->packer->unpack($data);
    }

    public function set($key, $value, $ttl = null): bool
    {
        if (is_array($value)) {
            $data = $value;
        } elseif ($value instanceof Arrayable) {
            $data = $value->toArray();
        } else {
            throw new CacheException('The value must is array.');
        }

        $serialized = $this->packer->pack($data);
        if ($ttl) {
            $seconds = $this->secondsUntil($ttl);
            if ($seconds > 0) {
                return $this->redis->set($key, $serialized, ['EX' => $seconds]);
            }
        }
        return $this->redis->set($key, $serialized);
    }

    public function delete($key): bool
    {
        return (bool) $this->redis->del($key);
    }

    public function clear(): bool
    {
        throw new CacheException('Method clear is forbidden.');
    }

    /**
     * @param iterable $keys
     * @param mixed $default
     * @return array|iterable
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $data = $this->redis->mget((array) $keys);
        $result = [];
        foreach ($data as $item) {
            if (! empty($item)) {
                $result[] = $this->packer->unpack($item);
            }
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        throw new CacheException('Method setMultiple is forbidden.');
    }

    public function deleteMultiple($keys): bool
    {
        return (bool) $this->redis->del(...$keys);
    }

    public function has($key): bool
    {
        return (bool) $this->redis->exists($key);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function incr($key, $column, $amount): bool
    {
        return $this->delete($key);
    }
}
