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

use Hyperf\ModelCache\Config;
use Hyperf\ModelCache\Exception\CacheException;
use Hyperf\ModelCache\Redis\HashGetMultiple;
use Hyperf\ModelCache\Redis\HashIncr;
use Hyperf\ModelCache\Redis\LuaManager;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\InteractsWithTime;
use Psr\Container\ContainerInterface;

class RedisHandler implements HandlerInterface
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
     * @var LuaManager
     */
    protected $manager;

    protected $defaultKey = 'HF-DATA';

    protected $defaultValue = 'DEFAULT';

    public function __construct(ContainerInterface $container, Config $config)
    {
        $this->container = $container;
        if (! $container->has(RedisProxy::class)) {
            throw new CacheException(sprintf('Entry[%s] of the container is not exist.', RedisProxy::class));
        }

        $this->redis = make(RedisProxy::class, ['pool' => $config->getPool()]);
        $this->config = $config;
        $this->manager = make(LuaManager::class, [$config]);
    }

    public function get($key, $default = null)
    {
        $data = $this->redis->hGetAll($key);
        if (! $data) {
            return $default;
        }

        unset($data[$this->defaultKey]);

        if (empty($data)) {
            return [];
        }

        return $data;
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

        $data = array_merge($data, [$this->defaultKey => $this->defaultValue]);
        $res = $this->redis->hMSet($key, $data);
        if ($ttl) {
            $seconds = $this->secondsUntil($ttl);
            if ($seconds > 0) {
                $this->redis->expire($key, $seconds);
            }
        }

        return $res;
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
        $data = $this->manager->handle(HashGetMultiple::class, $keys);
        $result = [];
        foreach ($data as $item) {
            unset($item[$this->defaultKey]);
            if (! empty($item)) {
                $result[] = $item;
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
        $data = $this->manager->handle(HashIncr::class, [$key, $column, $amount], 1);

        return is_numeric($data);
    }
}
