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
use malkusch\lock\mutex\Mutex;
use malkusch\lock\mutex\PHPRedisMutex;
use Psr\SimpleCache\InvalidArgumentException;

use function Hyperf\Support\make;

class RedisStorage implements Storage, GlobalScope
{
    public const KEY_PREFIX = 'rateLimiter:storage:';

    private Mutex $mutex;

    /**
     * @var string the key
     */
    private $key;

    public function __construct(string $key, private Redis $redis, $timeout = 0)
    {
        $key = self::KEY_PREFIX . $key;
        $this->key = $key;
        $this->mutex = make(PHPRedisMutex::class, [
            'redisAPIs' => [$redis],
            'name' => $key,
            'timeout' => $timeout,
        ]);
    }

    public function bootstrap($microtime)
    {
        $this->setMicrotime($microtime);
    }

    public function isBootstrapped()
    {
        try {
            return (bool) $this->redis->exists($this->key);
        } catch (InvalidArgumentException $e) {
            throw new StorageException('Failed to check for key existence', 0, $e);
        }
    }

    public function remove()
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
    public function setMicrotime($microtime)
    {
        try {
            $data = DoublePacker::pack($microtime);

            if (! $this->redis->set($this->key, $data)) {
                throw new StorageException('Failed to store microtime');
            }
        } catch (InvalidArgumentException $e) {
            throw new StorageException('Failed to store microtime', 0, $e);
        }
    }

    /**
     * @SuppressWarnings(PHPMD)
     * @return float
     * @throws StorageException
     */
    public function getMicrotime()
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

    public function getMutex()
    {
        return $this->mutex;
    }

    public function letMicrotimeUnchanged()
    {
    }
}
