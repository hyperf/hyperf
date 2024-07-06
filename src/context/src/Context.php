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

use ArrayObject;
use Closure;
use Hyperf\Engine\Coroutine;

use function Hyperf\Support\value;

/**
 * @template TKey of string
 * @template TValue
 */
class Context
{
    /**
     * @var array<TKey, TValue>
     */
    protected static array $nonCoContext = [];

    /**
     * @param TKey $id
     * @param TValue $value
     * @return TValue
     */
    public static function set(string $id, mixed $value, ?int $coroutineId = null): mixed
    {
        if (Coroutine::id() > 0) {
            Coroutine::getContextFor($coroutineId)[$id] = $value;
        } else {
            static::$nonCoContext[$id] = $value;
        }

        return $value;
    }

    /**
     * @param TKey $id
     * @return TValue
     */
    public static function get(string $id, mixed $default = null, ?int $coroutineId = null): mixed
    {
        if (Coroutine::id() > 0) {
            return Coroutine::getContextFor($coroutineId)[$id] ?? $default;
        }

        return static::$nonCoContext[$id] ?? $default;
    }

    /**
     * @param TKey $id
     */
    public static function has(string $id, ?int $coroutineId = null): bool
    {
        if (Coroutine::id() > 0) {
            return isset(Coroutine::getContextFor($coroutineId)[$id]);
        }

        return isset(static::$nonCoContext[$id]);
    }

    /**
     * Release the context when you are not in coroutine environment.
     *
     * @param TKey $id
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
     *
     * @param TKey $id
     * @param (Closure(TValue):TValue) $closure
     */
    public static function override(string $id, Closure $closure, ?int $coroutineId = null): mixed
    {
        $value = null;

        if (self::has($id, $coroutineId)) {
            $value = self::get($id, null, $coroutineId);
        }

        $value = $closure($value);

        self::set($id, $value, $coroutineId);

        return $value;
    }

    /**
     * Retrieve the value and store it if not exists.
     *
     * @param TKey $id
     * @param TValue $value
     * @return TValue
     */
    public static function getOrSet(string $id, mixed $value, ?int $coroutineId = null): mixed
    {
        if (! self::has($id, $coroutineId)) {
            return self::set($id, value($value), $coroutineId);
        }

        return self::get($id, null, $coroutineId);
    }

    /**
     * @return null|array<TKey, TValue>|ArrayObject<TKey, TValue>
     */
    public static function getContainer(?int $coroutineId = null)
    {
        if (Coroutine::id() > 0) {
            return Coroutine::getContextFor($coroutineId);
        }

        return static::$nonCoContext;
    }
}
