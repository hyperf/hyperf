<?php

namespace Hyperflex\Utils;


use Hyperflex\Utils\Traits\Container;

class Context
{
    use Container;

    /**
     * @inheritdoc
     */
    public static function set($id, $value)
    {
        static::$container[static::getCoroutineId()][$id] = $value;
    }

    /**
     * @inheritdoc
     */
    public static function get($id)
    {
        return static::$container[static::getCoroutineId()][$id];
    }

    /**
     * @inheritdoc
     */
    public static function has($id)
    {
        return isset(static::$container[static::getCoroutineId()][$id]);
    }

    /**
     * Destroy the coroutine context
     *
     * @param int|null $coroutineId If provide a coroutine ID, then will destroy the specified context.
     */
    public static function destroy(int $coroutineId = null)
    {
        if (! $coroutineId) {
            $coroutineId = static::getCoroutineId();
        }
        unset(static::$container[$coroutineId]);
    }

    private static function getCoroutineId()
    {
        return Coroutine::tid();
    }

}