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
use bandwidthThrottle\tokenBucket\storage\StorageException;
use bandwidthThrottle\tokenBucket\TokenBucket;
use Hyperf\Contract\ConfigInterface;
use Hyperf\RateLimit\Storage\RedisStorage;
use Hyperf\RateLimit\Storage\StorageInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class RateLimitHandler
{
    public const RATE_LIMIT_BUCKETS = 'rateLimit:buckets';

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @throws StorageException
     */
    public function build(string $key, int $limit, int $capacity, int $timeout): TokenBucket
    {
        $config = $this->container->get(ConfigInterface::class);

        $storageClass = $config->get('rate_limit.storage.class', RedisStorage::class);

        $storage = match (gettype($storageClass)) {
            'string' => make($storageClass, ['key' => $key, 'timeout' => $timeout, 'options' => $config->get('rate_limit.storage.options', [])]),
            'object' => $storageClass,
            default => throw new InvalidArgumentException('Invalid configuration of rate limit storage.'),
        };
        if (! $storage instanceof StorageInterface) {
            throw new InvalidArgumentException('The storage of rate limit must be an instance of ' . StorageInterface::class);
        }

        $rate = make(Rate::class, ['tokens' => $limit, 'unit' => Rate::SECOND]);
        $bucket = make(TokenBucket::class, ['capacity' => $capacity, 'rate' => $rate, 'storage' => $storage]);
        $bucket->bootstrap($capacity);
        return $bucket;
    }
}
