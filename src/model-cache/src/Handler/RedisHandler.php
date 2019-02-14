<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ModelCache\Handler;

use Redis;
use Hyperf\ModelCache\Config;
use Hyperf\Utils\Contracts\Arrayable;
use Psr\Container\ContainerInterface;
use Hyperf\ModelCache\Redis\HashsGetMultiple;
use Hyperf\ModelCache\Exception\CacheException;

class RedisHandler implements HandlerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var HashsGetMultiple
     */
    protected $multiple;

    protected $luaSha = '';

    protected $defaultKey = 'HF-DATA';

    protected $defaultValue = 'DEFAULT';

    public function __construct(ContainerInterface $container, Config $config)
    {
        $this->container = $container;
        if (! $container->has(Redis::class)) {
            throw new CacheException(sprintf('Entry[%s] of the container is not exist.', Redis::class));
        }

        $this->redis = $container->get(Redis::class);
        $this->config = $config;
        $this->multiple = new HashsGetMultiple();
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
        if ($ttl && $ttl > 0) {
            $this->redis->expire($key, $ttl);
        }

        return $res;
    }

    public function delete($key)
    {
        return $this->redis->delete($key);
    }

    public function clear()
    {
        throw new CacheException('Method clear is forbidden.');
    }

    public function getMultiple($keys, $default = null)
    {
        if ($this->config->isLoadScript()) {
            $sha = $this->getLuaSha();
        }

        if (! empty($sha)) {
            $list = $this->redis->evalSha($sha, $keys, count($keys));
        } else {
            $script = $this->multiple->getScript();
            $list = $this->redis->eval($script, $keys, count($keys));
        }

        $result = [];
        foreach ($this->multiple->parseResponse($list) as $item) {
            unset($item[$this->defaultKey]);
            if ($item) {
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
        return $this->redis->delete(...$keys) > 0;
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
        $ret = $this->redis->hIncrByFloat($key, $column, (float) $amount);
        return is_float($ret);
    }

    protected function getLuaSha()
    {
        if (! empty($this->luaSha)) {
            return $this->luaSha;
        }

        $sha = $this->redis->script('load', $this->multiple->getScript());

        return $this->luaSha = $sha;
    }
}
