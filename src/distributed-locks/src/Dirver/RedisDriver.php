<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DistributedLocks\Driver;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;
use Redis;

class RedisDriver extends Driver
{

    /**
     * @var array
     */
    protected $redisPools;

    /**
     * @var
     */
    protected $config;

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);

        $this->redis      = $container->get(Redis::class);
        $this->config     = $container->get(ConfigInterface::class);
        $this->redisPools = $this->config->get('distributed-locks.redis.pools', []);
    }

    public function lock($resource, $ttl)
    {
        $mutexKey = $this->getMutexKey($resource);
        $token    = uniqid();
        $retry    = $this->retryCount;
        do {
            $n         = 0;
            $startTime = microtime(true) * 1000;
            foreach ($this->redisPools as $redisPool) {
                $redis = $this->container->get(RedisFactory::class)->get($redisPool);
                if ($this->lockRedis($redis, $mutexKey, $token, $ttl)) {
                    $n++;
                }
            }
            # Add 2 milliseconds to the drift to account for Redis expires
            # precision, which is 1 millisecond, plus 1 millisecond min drift
            # for small TTLs.
            $drift        = ($ttl * $this->clockDriftFactor) + 2;
            $validityTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;
            if ($n >= $this->quorum && $validityTime > 0) {
                return [
                    'validity' => $validityTime,
                    'resource' => $mutexKey,
                    'token'    => $token,
                ];
            } else {
                foreach ($this->redisPools as $redisPool) {
                    $this->unlockRedis($redisPool, $mutexKey, $token);
                }
            }
            // Wait a random delay before to retry
            $delay = mt_rand(floor($this->retryDelay / 2), $this->retryDelay);
            usleep($delay * 1000);
            $retry--;
        } while ($retry > 0);

        return false;
    }

    public function unlock(array $lock)
    {
        $resource = $lock['resource'];
        $token    = $lock['token'];
        $mutexKey = $this->getMutexKey($resource);
        foreach ($this->redisPools as $redisPool) {
            $redis = $this->container->get(RedisFactory::class)->get($redisPool);
            $this->unlockRedis($redis, $mutexKey, $token);
        }
    }

    /**
     * @param Redis $client
     * @param       $resource
     * @param       $token
     * @param       $ttl
     * @return bool
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    private function lockRedis(Redis $client, $resource, $token, $ttl)
    {
        return $client->set($resource, $token, ['NX', 'PX' => $ttl]);
    }

    /**
     * @param Redis $client
     * @param       $resource
     * @param       $token
     * @return mixed
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    private function unlockRedis(Redis $client, $resource, $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';

        return $client->eval($script, [$resource, $token], 1);
    }
}
