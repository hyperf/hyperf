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

use Hyperf\Contract\Arrayable;
use Hyperf\ModelCache\Config;
use Hyperf\ModelCache\Exception\CacheException;
use Hyperf\ModelCache\Redis\HashGetMultiple;
use Hyperf\ModelCache\Redis\HashIncr;
use Hyperf\ModelCache\Redis\LuaManager;
use Hyperf\Redis\RedisProxy;
use Hyperf\Support\Traits\InteractsWithTime;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class RedisHandler implements HandlerInterface, DefaultValueInterface
{
    use InteractsWithTime;

    protected RedisProxy $redis;

    protected LuaManager $manager;

    protected string $defaultKey = 'HF-DATA';

    protected string $defaultValue = 'DEFAULT';

    public function __construct(protected ContainerInterface $container, protected Config $config)
    {
        if (! $container->has(RedisProxy::class)) {
            throw new CacheException(sprintf('Entry[%s] of the container is not exist.', RedisProxy::class));
        }

        $this->redis = make(RedisProxy::class, ['pool' => $config->getPool()]);
        $this->manager = make(LuaManager::class, [$config]);
    }

    public function get($key, $default = null): mixed
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

    public function set($key, $value, $ttl = null): bool
    {
        if (is_array($value)) {
            $data = $value;
        } elseif ($value instanceof Arrayable) {
            $data = $value->toArray();
        } else {
            throw new CacheException('The value must is array.');
        }

        $data = array_merge([$this->defaultKey => $this->defaultValue], $data);
        $res = $this->redis->hMSet($key, $data);
        if ($ttl) {
            $seconds = $this->secondsUntil($ttl);
            if ($seconds > 0) {
                $this->redis->expire($key, $seconds);
            }
        }

        return $res;
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
        $data = $this->manager->handle(HashGetMultiple::class, (array) $keys);
        $result = [];
        foreach ($data as $item) {
            if (! empty($item)) {
                $result[] = $item;
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
        return $this->redis->del(...$keys) > 0;
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
        $data = $this->manager->handle(HashIncr::class, [$key, $column, $amount], 1);

        return is_numeric($data);
    }

    public function defaultValue(mixed $primaryValue): array
    {
        return [
            $this->defaultKey => $primaryValue,
        ];
    }

    public function isDefaultValue(array $data): bool
    {
        $value = current($data);
        return $this->defaultValue($value) === $data;
    }

    public function getPrimaryValue(array $data): mixed
    {
        return current($data);
    }

    public function clearDefaultValue(array $data): array
    {
        unset($data[$this->defaultKey]);
        return $data;
    }
}
