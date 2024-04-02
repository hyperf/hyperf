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

namespace Hyperf\Memory;

use RuntimeException;
use Swoole\Lock;

class LockManager
{
    /**
     * A container that use to store Lock.
     */
    private static array $container = [];

    /**
     * You should initialize a Lock with the identifier before use it.
     */
    public static function initialize(string $identifier, int $type = SWOOLE_RWLOCK, string $filename = ''): void
    {
        static::$container[$identifier] = new Lock($type, $filename);
    }

    /**
     * Get an initialized Lock from container by the identifier.
     *
     * @throws RuntimeException when the Lock with the identifier has not initialization
     */
    public static function get(string $identifier): Lock
    {
        if (! isset(static::$container[$identifier])) {
            throw new RuntimeException('The Lock has not initialization yet.');
        }

        return static::$container[$identifier];
    }

    /**
     * Remove the Lock by the identifier from container after used,
     * otherwise will occur memory leaks.
     */
    public static function clear(string $identifier): void
    {
        unset(static::$container[$identifier]);
    }
}
