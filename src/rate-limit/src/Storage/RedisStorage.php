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

namespace Hyperf\RateLimit\Storage;

use bandwidthThrottle\tokenBucket\storage\scope\GlobalScope;
use bandwidthThrottle\tokenBucket\storage\Storage;
use bandwidthThrottle\tokenBucket\storage\StorageException;
use bandwidthThrottle\tokenBucket\util\DoublePacker;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use malkusch\lock\mutex\Mutex;
use malkusch\lock\mutex\PHPRedisMutex;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\InvalidArgumentException;

use function Hyperf\Support\make;

class RedisStorage implements Storage, GlobalScope, StorageInterface
{
    public const KEY_PREFIX = 'rateLimiter:storage:';

    private Mutex $mutex;

    /**
     * @var string the key
     */
    private $key;

    private Redis $redis;

    private array $options;

    public function __construct(protected ContainerInterface $container, string $key, $timeout = 0, array $options = [])
    {
        $this->redis = $container->get(RedisFactory::class)->get(
            $options['pool'] ?? 'default'
        );
        $this->options = $options;
        $this->key = self::KEY_PREFIX . $key;
        $this->mutex = make(PHPRedisMutex::class, [
            'redisAPIs' => [$this->redis],
            'name' => $this->key,
            'timeout' => $timeout,
        ]);
    }

    public function bootstrap($microtime): void
    {
        $this->setMicrotime($microtime);
    }

    public function isBootstrapped(): bool
    {
        try {
            return (bool) $this->redis->exists($this->key);
        } catch (InvalidArgumentException $e) {
            throw new StorageException('Failed to check for key existence', 0, $e);
        }
    }

    public function remove(): void
    {
        try {
            if (! $this->redis->del($this->key)) {
                throw new StorageException('Failed to delete key');
            }
        } catch (InvalidArgumentException $e) {
            throw new StorageException('Failed to delete key', 0, $e);
        }
    }

    /**
     * @SuppressWarnings(PHPMD)
     * @param float $microtime
     * @throws StorageException
     */
    public function setMicrotime($microtime): void
    {
        try {
            $data = DoublePacker::pack($microtime);

            if (! $this->redis->set($this->key, $data)) {
                throw new StorageException('Failed to store microtime');
            }
            if (! empty($this->options['expired_time']) && $this->options['expired_time'] > 0) {
                $this->redis->expire($this->key, $this->options['expired_time']);
            }
        } catch (InvalidArgumentException $e) {
            throw new StorageException('Failed to store microtime', 0, $e);
        }
    }

    /**
     * @SuppressWarnings(PHPMD)
     * @throws StorageException
     */
    public function getMicrotime(): float
    {
        try {
            $data = $this->redis->get($this->key);
            if ($data === false) {
                throw new StorageException('Failed to get microtime');
            }
            return DoublePacker::unpack($data);
        } catch (InvalidArgumentException $e) {
            throw new StorageException('Failed to get microtime', 0, $e);
        }
    }

    public function getMutex(): Mutex
    {
        return $this->mutex;
    }

    public function letMicrotimeUnchanged(): void
    {
    }
}
