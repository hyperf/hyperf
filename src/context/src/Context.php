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
namespace Hyperf\Context;

use Closure;
use Hyperf\Engine\Coroutine;

use function Hyperf\Support\value;

class Context
{
    protected static array $nonCoContext = [];

    public static function set(string $id, mixed $value, ?int $coroutineId = null): mixed
    {
        if (Coroutine::id() > 0) {
            Coroutine::getContextFor($coroutineId)[$id] = $value;
        } else {
            static::$nonCoContext[$id] = $value;
        }

        return $value;
    }

    public static function get(string $id, mixed $default = null, ?int $coroutineId = null): mixed
    {
        if (Coroutine::id() > 0) {
            return Coroutine::getContextFor($coroutineId)[$id] ?? $default;
        }

        return static::$nonCoContext[$id] ?? $default;
    }

    public static function has(string $id, ?int $coroutineId = null): bool
    {
        if (Coroutine::id() > 0) {
            return isset(Coroutine::getContextFor($coroutineId)[$id]);
        }

        return isset(static::$nonCoContext[$id]);
    }

    /**
     * Release the context when you are not in coroutine environment.
     */
    public static function destroy(string $id, ?int $coroutineId = null): void
    {
        if (Coroutine::id() > 0) {
            unset(Coroutine::getContextFor($coroutineId)[$id]);
        }

        unset(static::$nonCoContext[$id]);
    }

    /**
     * Copy the context from a coroutine to current coroutine.
     * This method will delete the origin values in current coroutine.
     */
    public static function copy(int $fromCoroutineId, array $keys = []): void
    {
        $from = Coroutine::getContextFor($fromCoroutineId);

        if ($from === null) {
            return;
        }

        $current = Coroutine::getContextFor();

        if ($keys) {
            $map = array_intersect_key($from->getArrayCopy(), array_flip($keys));
        } else {
            $map = $from->getArrayCopy();
        }

        $current->exchangeArray($map);
    }

    /**
     * Retrieve the value and override it by closure.
     */
    public static function override(string $id, Closure $closure, ?int $coroutineId = null): mixed
    {
        $value = null;

        if (self::has($id, $coroutineId)) {
            $value = self::get($id, $coroutineId);
        }

        $value = $closure($value);

        self::set($id, $value, $coroutineId);

        return $value;
    }

    /**
     * Retrieve the value and store it if not exists.
     */
    public static function getOrSet(string $id, mixed $value, ?int $coroutineId = null): mixed
    {
        if (! self::has($id, $coroutineId)) {
            return self::set($id, value($value), $coroutineId);
        }

        return self::get($id, $coroutineId);
    }

    public static function getContainer(?int $coroutineId = null)
    {
        if (Coroutine::id() > 0) {
            return Coroutine::getContextFor($coroutineId);
        }

        return static::$nonCoContext;
    }
}
