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
namespace Hyperf\RateLimit\Handler;

use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use Hyperf\RateLimit\Storage\RedisStorage;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class RateLimitHandler
{
    public const RATE_LIMIT_BUCKETS = 'rateLimit:buckets';

    private Redis $redis;

    public function __construct(ContainerInterface $container)
    {
        $this->redis = $container->get(Redis::class);
    }

    /**
     * @throws \bandwidthThrottle\tokenBucket\storage\StorageException
     */
    public function build(string $key, int $limit, int $capacity, int $timeout): TokenBucket
    {
        $storage = make(RedisStorage::class, ['key' => $key, 'redis' => $this->redis, 'timeout' => $timeout]);
        $rate = make(Rate::class, ['tokens' => $limit, 'unit' => Rate::SECOND]);
        $bucket = make(TokenBucket::class, ['capacity' => $capacity, 'rate' => $rate, 'storage' => $storage]);
        $bucket->bootstrap($capacity);
        return $bucket;
    }
}
