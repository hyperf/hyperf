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

namespace Hyperf\Utils;

use Carbon\Carbon;
use Swoole\Coroutine as SwCoroutine;

class Context
{
    public const DONE = 'hyperf.context.done';

    protected static $nonCoContext = [];

    public static function set(string $id, $value)
    {
        if (Coroutine::inCoroutine()) {
            SwCoroutine::getContext()[$id] = $value;
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
        /**
         * @var \ArrayObject
         * @var \ArrayObject $current
         */
        $from = SwCoroutine::getContext($fromCoroutineId);
        $current = SwCoroutine::getContext();
        $current->exchangeArray($keys ? array_fill_keys($keys, $from->getArrayCopy()) : $from->getArrayCopy());
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

    public static function done(): bool
    {
        $holder = self::getOrSet(static::DONE, new \stdClass());
        return $holder->done ?? false;
    }

    /**
     * @param float|int $millisecond
     */
    public static function setTimeout($millisecond)
    {
        $holder = self::getOrSet(static::DONE, new \stdClass());
        Coroutine::create(function () use ($holder, $millisecond) {
            usleep((int) $millisecond * 1000);
            $holder->done = true;
        });
    }

    public static function setDeadline(\DateTime $deadline)
    {
        if (! ($deadline instanceof Carbon)) {
            $deadline = Carbon::instance($deadline);
        }

        $timeout = $deadline->getPreciseTimestamp(3) - microtime(true) * 1000;
        $timeout = $timeout > 0 ? $timeout : 0;
        static::setTimeout($timeout);
    }

    public static function cancel(): void
    {
        $holder = self::getOrSet(static::DONE, new \stdClass());
        $holder->done = true;
    }

    public static function go(callable $callable): int
    {
        if (! self::has(static::DONE)) {
            self::set(static::DONE, new \stdClass());
        }
        return Coroutine::create(function () use ($callable) {
            static::copy(coroutine::parentId());
            $callable();
        });
    }
}
