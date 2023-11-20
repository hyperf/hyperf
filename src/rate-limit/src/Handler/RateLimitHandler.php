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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Support\make;

class RateLimitHandler
{
    public const RATE_LIMIT_BUCKETS = 'rateLimit:buckets';

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @throws StorageException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function build(string $key, int $limit, int $capacity, int $timeout): TokenBucket
    {
        $config = $this->container->get(ConfigInterface::class);

        $storage = make(
            $config->get('rate_limit.storage.class', RedisStorage::class),
            ['key' => $key, 'timeout' => $timeout, 'constructor' => $config->get('rate_limit.storage.constructor', [])]
        );
        $rate = make(Rate::class, ['tokens' => $limit, 'unit' => Rate::SECOND]);
        $bucket = make(TokenBucket::class, ['capacity' => $capacity, 'rate' => $rate, 'storage' => $storage]);
        $bucket->bootstrap($capacity);
        return $bucket;
    }
}
