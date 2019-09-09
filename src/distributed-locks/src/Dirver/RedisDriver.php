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

use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;
use Redis;

class RedisDriver extends Driver
{
    /**
     * @var int
     */
    protected $quorum;

    /**
     * @var int
     */
    protected $retryDelay;

    /**
     * @var int
     */
    protected $retryCount;

    /**
     * @var float
     */
    protected $clockDriftFactor = 0.01;

    /**
     * @var array
     */
    protected $pools;

    /**
     * @var
     */
    protected $config;

    public function __construct(ContainerInterface $container, array $config, string $prefix)
    {
        parent::__construct($container, $config, $prefix);

        $this->pools      = $config['pools'] ?? [];
        $this->retryDelay = $config['retry_delay'] ?? 200;
        $this->retryCount = $config['retry_count'] ?? 0;
        $this->quorum     = min(count($this->pools), (count($this->pools) / 2 + 1));
    }

    /**
     * @param $resource
     * @param $ttl
     * @return array|bool
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function lock($resource, $ttl)
    {
        $mutexKey = $this->getMutexKey($resource);
        $token    = uniqid();
        $retry    = $this->retryCount;
        do {
            $n         = 0;
            $startTime = microtime(true) * 1000;
            foreach ($this->pools as $pool) {
                $redis = $this->container->get(RedisFactory::class)->get($pool);
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
                foreach ($this->pools as $pool) {
                    $this->unlockRedis($pool, $mutexKey, $token);
                }
            }
            // Wait a random delay before to retry
            $delay = mt_rand(floor($this->retryDelay / 2), $this->retryDelay);
            usleep($delay * 1000);
            $retry--;
        } while ($retry > 0);

        return false;
    }

    /**
     * @param array $lock
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function unlock(array $lock)
    {
        $resource = $lock['resource'];
        $token    = $lock['token'];
        foreach ($this->pools as $pool) {
            $redis = $this->container->get(RedisFactory::class)->get($pool);
            $this->unlockRedis($redis, $resource, $token);
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
