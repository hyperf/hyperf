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

use Hyperf\Contract\PackerInterface;
use Hyperf\ModelCache\Config;
use Hyperf\ModelCache\Exception\CacheException;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\InteractsWithTime;
use Hyperf\Utils\Packer\PhpSerializerPacker;
use Psr\Container\ContainerInterface;

class RedisStringHandler implements HandlerInterface
{
    use InteractsWithTime;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RedisProxy
     */
    protected $redis;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PackerInterface
     */
    protected $packer;

    public function __construct(ContainerInterface $container, Config $config)
    {
        $this->container = $container;
        if (! $container->has(RedisProxy::class)) {
            throw new CacheException(sprintf('Entry[%s] of the container is not exist.', RedisProxy::class));
        }

        $this->redis = make(RedisProxy::class, ['pool' => $config->getPool()]);
        $this->config = $config;
        $this->packer = $container->get(PhpSerializerPacker::class);
    }

    public function get($key, $default = null)
    {
        $data = $this->redis->get($key);
        if (! $data) {
            return $default;
        }

        return $this->packer->unpack($data);
    }

    public function set($key, $value, $ttl = null)
    {
        if (is_array($value)) {
            $data = $value;
        } elseif ($value instanceof Arrayable) {
            $data = $value->toArray();
        } else {
            throw new CacheException(sprintf('The value must is array.'));
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

    public function delete($key)
    {
        return (bool) $this->redis->del($key);
    }

    public function clear()
    {
        throw new CacheException('Method clear is forbidden.');
    }

    public function getMultiple($keys, $default = null)
    {
        $data = $this->redis->mget($keys);
        $result = [];
        foreach ($data as $item) {
            if (! empty($item)) {
                $result[] = $this->packer->unpack($item);
            }
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        throw new CacheException('Method setMultiple is forbidden.');
    }

    public function deleteMultiple($keys)
    {
        return $this->redis->del(...$keys) > 0;
    }

    public function has($key)
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
