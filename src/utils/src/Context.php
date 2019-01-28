<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Utils;

use Hyperf\Utils\Traits\Container;

class Context
{
    use Container;

    /**
     * @var array
     */
    protected static $container = [];

    /**
     * {@inheritdoc}
     */
    public static function set($id, $value)
    {
        static::$container[static::getCoroutineId()][$id] = $value;
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function get($id)
    {
        return static::$container[static::getCoroutineId()][$id];
    }

    /**
     * {@inheritdoc}
     */
    public static function has($id)
    {
        return isset(static::$container[static::getCoroutineId()][$id]);
    }

    /**
     * Destroy the coroutine context.
     *
     * @param null|int $coroutineId if provide a coroutine ID, then will destroy the specified context
     */
    public static function destroy(int $coroutineId = null)
    {
        if (! $coroutineId) {
            $coroutineId = static::getCoroutineId();
        }
        unset(static::$container[$coroutineId]);
    }

    /**
     * Copy the context from a coroutine to another coroutine,
     * Notice that this method is not a deep copy and I/O connection cannot copy to a another coroutine.
     */
    public static function copy(int $fromCoroutineId, int $toCoroutineId = null): void
    {
        if (! $toCoroutineId) {
            $toCoroutineId = static::getCoroutineId();
        }
        static::$container[$toCoroutineId] = static::$container[$fromCoroutineId];
    }

    public static function getContainer()
    {
        return static::$container;
    }

    protected static function getCoroutineId()
    {
        return Coroutine::id();
    }
}
