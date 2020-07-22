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
use SessionHandlerInterface;

class RedisHandler implements SessionHandlerInterface
{
    /**
     * @var \Hyperf\Redis\Redis|\Predis\Client|\Redis|\RedisArray|\RedisCluster
     */
    protected $redis;

    /**
     * @var int
     */
    protected $gcMaxLifeTime = 1200;

    public function __construct($redis, int $gcMaxLifeTime)
    {
        if (! $redis instanceof \Redis && ! $redis instanceof \RedisArray && ! $redis instanceof \RedisCluster && ! $redis instanceof \Predis\Client && ! $redis instanceof RedisProxy) {
            throw new \InvalidArgumentException(sprintf('%s() expects parameter 1 to be Redis, RedisArray, RedisCluster, Predis\Client or Hyperf\Redis\Redis, %s given', __METHOD__, \is_object($redis) ? \get_class($redis) : \gettype($redis)));
        }

        $this->redis = $redis;
        $this->gcMaxLifeTime = $gcMaxLifeTime;
    }

    /**
     * Close the session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy a session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id the session ID being destroyed
     * @return bool
     */
    public function destroy($session_id)
    {
        $this->redis->del($session_id);
        return true;
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * Initialize session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path the path where to store/retrieve the session
     * @param string $name the session name
     * @return bool
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * Read session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id the session id to read data for
     * @return string
     */
    public function read($session_id)
    {
        return $this->redis->get($session_id) ?: '';
    }

    /**
     * Write session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id the session id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        return (bool) $this->redis->setEx($session_id, (int) $this->gcMaxLifeTime, $session_data);
    }
}
