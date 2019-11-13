<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RateLimit\Storage;

use bandwidthThrottle\tokenBucket\storage\scope\GlobalScope;
use bandwidthThrottle\tokenBucket\storage\Storage;
use bandwidthThrottle\tokenBucket\storage\StorageException;
use bandwidthThrottle\tokenBucket\util\DoublePacker;
use malkusch\lock\mutex\Mutex;
use malkusch\lock\mutex\PHPRedisMutex;
use Psr\SimpleCache\InvalidArgumentException;
use Redis;
use function make;

class RedisStorage implements Storage, GlobalScope
{
    const KEY_PREFIX = 'rateLimiter:storage:';

    /**
     * @var Mutex
     */
    private $mutex;

    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var string the key
     */
    private $key;

    public function __construct($key, $redis, $timeout = 0)
    {
        $key = self::KEY_PREFIX . $key;
        $this->key = $key;
        $this->redis = $redis;
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
            return $this->redis->exists($this->key);
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
     * @throws StorageException
     * @return float
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
