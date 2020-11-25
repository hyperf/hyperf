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

use Swoole\Coroutine as SwCoroutine;

class Context
{
    protected static $nonCoContext = [];

    public static function set(string $id, $value, $coroutineId = null)
    {
        if (Coroutine::inCoroutine()) {
            if ($coroutineId !== null) {
                SwCoroutine::getContext($coroutineId)[$id] = $value;
            } else {
                SwCoroutine::getContext()[$id] = $value;
            }
        } else {
            static::$nonCoContext[$id] = $value;
        }
        return $value;
    }

    public static function get(string $id, $default = null, $coroutineId = null)
    {
        if (Coroutine::inCoroutine()) {
            if ($coroutineId !== null) {
                return SwCoroutine::getContext($coroutineId)[$id] ?? $default;
            }
            return SwCoroutine::getContext()[$id] ?? $default;
        }

        return static::$nonCoContext[$id] ?? $default;
    }

    public static function has(string $id, $coroutineId = null)
    {
        if (Coroutine::inCoroutine()) {
            if ($coroutineId !== null) {
                return isset(SwCoroutine::getContext($coroutineId)[$id]);
            }
            return isset(SwCoroutine::getContext()[$id]);
        }

        return isset(static::$nonCoContext[$id]);
    }

    public static function setGlobal(string $id, $value)
    {
        if (Coroutine::inCoroutine()) {
            $parentId = null;
            while (true) {
                $pid = is_null($parentId) ? SwCoroutine::getPcid() : SwCoroutine::getPcid($parentId);
                if ($pid == -1) {
                    break;
                }
                $parentId = $pid;
            }
            if (is_null($parentId)) {
                SwCoroutine::getContext()[$id] = $value;
            } else {
                SwCoroutine::getContext($parentId)[$id] = $value;
            }
            return $value;
        } else {
            static::$nonCoContext[$id] = $value;
        }
        return $value;
    }

    public static function getGlobal(string $id, $default = null)
    {
        if (Coroutine::inCoroutine()) {
            $parentId = null;
            while (true) {
                $pid = is_null($parentId) ? SwCoroutine::getPcid() : SwCoroutine::getPcid($parentId);
                if ($pid == -1) {
                    break;
                }
                $parentId = $pid;
            }
            if (is_null($parentId)) {
                return SwCoroutine::getContext()[$id] ?? $default;
            } else {
                return SwCoroutine::getContext($parentId)[$id] ?? $default;
            }
        }

        return static::$nonCoContext[$id] ?? $default;
    }

    public static function hasGlobal(string $id)
    {
        if (Coroutine::inCoroutine()) {
            $parentId = null;
            while (true) {
                $pid = is_null($parentId) ? SwCoroutine::getPcid() : SwCoroutine::getPcid($parentId);
                if ($pid == -1) {
                    break;
                }
                $parentId = $pid;
            }
            if (is_null($parentId)) {
                return isset(SwCoroutine::getContext()[$id]);
            } else {
                return isset(SwCoroutine::getContext($parentId)[$id]);
            }
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
        /** @var \ArrayObject $from */
        $from = SwCoroutine::getContext($fromCoroutineId);
        /** @var \ArrayObject $current */
        $current = SwCoroutine::getContext();
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
            return SwCoroutine::getContext();
        }

        return static::$nonCoContext;
    }
}
