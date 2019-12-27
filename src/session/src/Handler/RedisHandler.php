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
     * @return bool <p>
     *              The return value (usually TRUE on success, FALSE on failure).
     *              Note this value is returned internally to PHP for processing.
     *              </p>
     * @since 5.4.0
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
     * @return bool <p>
     *              The return value (usually TRUE on success, FALSE on failure).
     *              Note this value is returned internally to PHP for processing.
     *              </p>
     * @since 5.4.0
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
     * @param int $maxlifetime <p>
     *                         Sessions that have not updated for
     *                         the last maxlifetime seconds will be removed.
     *                         </p>
     * @return bool <p>
     *              The return value (usually TRUE on success, FALSE on failure).
     *              Note this value is returned internally to PHP for processing.
     *              </p>
     * @since 5.4.0
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
     * @return bool <p>
     *              The return value (usually TRUE on success, FALSE on failure).
     *              Note this value is returned internally to PHP for processing.
     *              </p>
     * @since 5.4.0
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
     * @return string <p>
     *                Returns an encoded string of the read data.
     *                If nothing was read, it must return an empty string.
     *                Note this value is returned internally to PHP for processing.
     *                </p>
     * @since 5.4.0
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
     * @param string $session_data <p>
     *                             The encoded session data. This data is the
     *                             result of the PHP internally encoding
     *                             the $_SESSION superglobal to a serialized
     *                             string and passing it as this parameter.
     *                             Please note sessions use an alternative serialization method.
     *                             </p>
     * @return bool <p>
     *              The return value (usually TRUE on success, FALSE on failure).
     *              Note this value is returned internally to PHP for processing.
     *              </p>
     * @since 5.4.0
     */
    public function write($session_id, $session_data)
    {
        return (bool) $this->redis->setEx($session_id, (int) $this->gcMaxLifeTime, $session_data);
    }
}
