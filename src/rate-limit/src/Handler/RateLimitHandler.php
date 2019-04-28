<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RateLimit\Handler;

use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use Hyperf\RateLimit\Storage\CoRedisStorage;
use Psr\Container\ContainerInterface;

class RateLimitHandler
{
    const RATE_LIMIT_BUCKETS = 'rateLimit:buckets';

    /**
     * @var \Redis
     */
    private $redis;

    public function __construct(ContainerInterface $container)
    {
        $this->redis = $container->get(\Redis::class);
    }

    /**
     * @param string $key
     * @param int $limit
     * @param int $capacity
     * @param int $timeout
     * @throws \bandwidthThrottle\tokenBucket\storage\StorageException
     * @return TokenBucket
     */
    public function build(string $key, int $limit, int $capacity, int $timeout)
    {
        $storage = make(CoRedisStorage::class, ['key' => $key, 'redis' => $this->redis, 'timeout' => $timeout]);
        $rate = make(Rate::class, ['tokens' => $limit, 'unit' => Rate::SECOND]);
        $bucket = make(TokenBucket::class, ['capacity' => $capacity, 'rate' => $rate, 'storage' => $storage]);
        $bucket->bootstrap($capacity);
        return $bucket;
    }
}
