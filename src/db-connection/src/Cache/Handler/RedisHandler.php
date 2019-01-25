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

namespace Hyperf\DbConnection\Cache\Handler;

use Hyperf\DbConnection\Cache\Config;
use Hyperf\DbConnection\Cache\Exception\CacheException;
use Hyperf\DbConnection\Cache\Redis\HashsGetMultiple;
use Hyperf\Utils\Contracts\Arrayable;
use Psr\Container\ContainerInterface;
use Redis;

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

    protected $defaultHash = ['HF-DATA' => 'DEFAULT'];

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

        if ($data == $this->defaultHash) {
            return $default;
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

        $data = array_merge($data, $this->defaultHash);
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

        return $list;
    }

    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
    }

    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
    }

    public function has($key)
    {
        // TODO: Implement has() method.
    }

    public function getConfig(): Config
    {
        return $this->config;
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
