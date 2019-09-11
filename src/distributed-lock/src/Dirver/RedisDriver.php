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

namespace Hyperf\DistributedLock\Driver;

use Hyperf\DistributedLock\Exception\InvalidArgumentException;
use Hyperf\DistributedLock\Mutex;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
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
    protected $retry;

    /**
     * @var float
     */
    protected $driftFactor = 0.01;

    /**
     * @var array
     */
    protected $pools;

    /**
     * @var
     */
    protected $config;

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);

        $pools = $config['pools'] ?? [];
        if (empty($config)) {
            throw new InvalidArgumentException('The lock config redis pools can\'t be empty.');
        }
        $this->pools = $pools;
        $this->retry = $config['retry'] ?? 0;
        $this->retryDelay = $config['retry_delay'] ?? 200;
        $this->driftFactor = $config['drift_factor'] ?? 0.01;
        $this->quorum = min(count($this->pools), (count($this->pools) / 2 + 1));
    }

    /**
     * @param string $resource
     * @param int $ttl
     * @return Mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function lock(string $resource, int $ttl): Mutex
    {
        $token = uniqid();
        $retry = $this->retry;
        $mutex = new Mutex();
        do {
            $n = 0;
            $startTime = microtime(true) * 1000;
            foreach ($this->pools as $pool) {
                $redis = $this->container->get(RedisFactory::class)->get($pool);
                if ($this->lockRedis($redis, $resource, $token, $ttl)) {
                    ++$n;
                }
            }
            # Add 2 milliseconds to the drift to account for Redis expires
            # precision, which is 1 millisecond, plus 1 millisecond min drift
            # for small TTLs.
            $drift = ($ttl * $this->driftFactor) + 2;
            $validityTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;
            if ($n >= $this->quorum && $validityTime > 0) {
                return $mutex->setIsAcquired()
                    ->setContext([
                        'validity' => $validityTime,
                        'resource' => $resource,
                        'token' => $token,
                    ]);
            }
            foreach ($this->pools as $pool) {
                $redis = $this->container->get(RedisFactory::class)->get($pool);
                $this->unlockRedis($redis, $resource, $token);
            }

            // Wait a random delay before to retry
            $delay = mt_rand((int) floor($this->retryDelay / 2), $this->retryDelay);
            usleep($delay * 1000);
            --$retry;
        } while ($retry > 0);

        return $mutex;
    }

    /**
     * @param Mutex $mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function unlock(Mutex $mutex): void
    {
        $context = $mutex->getContext();
        $resource = $context['resource'] ?? '';
        $token = $context['token'] ?? '';
        foreach ($this->pools as $pool) {
            $redis = $this->container->get(RedisFactory::class)->get($pool);
            $this->unlockRedis($redis, $resource, $token);
        }
    }

    /**
     * @param RedisProxy $redis
     * @param $resource
     * @param $token
     * @param $ttl
     * @return bool
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    private function lockRedis(RedisProxy $redis, $resource, $token, $ttl)
    {
        return $redis->set($resource, $token, ['NX', 'PX' => $ttl]);
    }

    /**
     * @param RedisProxy $redis
     * @param $resource
     * @param $token
     * @return mixed
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    private function unlockRedis(RedisProxy $redis, $resource, $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';

        return $redis->eval($script, [$resource, $token], 1);
    }
}
