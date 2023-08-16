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
namespace Hyperf\Session\Handler;

use Hyperf\Redis\Redis as RedisProxy;
use InvalidArgumentException;
use Redis;
use RedisArray;
use RedisCluster;
use SessionHandlerInterface;

use function get_class;
use function gettype;
use function is_object;

class RedisHandler implements SessionHandlerInterface
{
    /**
     * @var \Hyperf\Redis\Redis|\Predis\Client|Redis|RedisArray|RedisCluster
     */
    protected $redis;

    public function __construct($redis, protected int $gcMaxLifeTime = 1200)
    {
        if (! $redis instanceof Redis && ! $redis instanceof RedisArray && ! $redis instanceof RedisCluster && ! $redis instanceof \Predis\Client && ! $redis instanceof RedisProxy) {
            throw new InvalidArgumentException(sprintf('%s() expects parameter 1 to be Redis, RedisArray, RedisCluster, Predis\Client or Hyperf\Redis\Redis, %s given', __METHOD__, is_object($redis) ? get_class($redis) : gettype($redis)));
        }

        $this->redis = $redis;
    }

    /**
     * Close the session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.close.php
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroy a session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $id the session ID being destroyed
     */
    public function destroy(string $id): bool
    {
        $this->redis->del($id);
        return true;
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     */
    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

    /**
     * Initialize session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $path the path where to store/retrieve the session
     * @param string $name the session name
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Read session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $id the session id to read data for
     * @return string
     */
    public function read(string $id): string|false
    {
        return $this->redis->get($id) ?: '';
    }

    /**
     * Write session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $id the session id
     */
    public function write(string $id, string $data): bool
    {
        return (bool) $this->redis->setEx($id, $this->gcMaxLifeTime, $data);
    }
}
