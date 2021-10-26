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
namespace Hyperf\Utils;

use Hyperf\Engine\Coroutine as Co;

class Context
{
    protected static $nonCoContext = [];

    public static function set(string $id, $value)
    {
        if (Coroutine::inCoroutine()) {
            Co::getContextFor()[$id] = $value;
        } else {
            static::$nonCoContext[$id] = $value;
        }
        return $value;
    }

    public static function get(string $id, $default = null, $coroutineId = null)
    {
        if (Coroutine::inCoroutine()) {
            return Co::getContextFor($coroutineId)[$id] ?? $default;
        }

        return static::$nonCoContext[$id] ?? $default;
    }

    public static function has(string $id, $coroutineId = null)
    {
        if (Coroutine::inCoroutine()) {
            return isset(Co::getContextFor($coroutineId)[$id]);
        }

        return isset(static::$nonCoContext[$id]);
    }

    /**
     * Release the context when you are not in coroutine environment.
     */
    public static function destroy(string $id)
    {
        unset(static::$nonCoContext[$id]);
    }

    /**
     * Copy the context from a coroutine to current coroutine.
     */
    public static function copy(int $fromCoroutineId, array $keys = []): void
    {
        $from = Co::getContextFor($fromCoroutineId);
        if ($from === null) {
            return;
        }

        $current = Co::getContextFor();
        $current->exchangeArray($keys ? Arr::only($from->getArrayCopy(), $keys) : $from->getArrayCopy());
    }

    /**
     * Retrieve the value and override it by closure.
     */
    public static function override(string $id, \Closure $closure)
    {
        $value = null;
        if (self::has($id)) {
            $value = self::get($id);
        }
        $value = $closure($value);
        self::set($id, $value);
        return $value;
    }

    /**
     * Retrieve the value and store it if not exists.
     * @param mixed $value
     */
    public static function getOrSet(string $id, $value)
    {
        if (! self::has($id)) {
            return self::set($id, value($value));
        }
        return self::get($id);
    }

    public static function getContainer()
    {
        if (Coroutine::inCoroutine()) {
            return Co::getContextFor();
        }

        return static::$nonCoContext;
    }
}
