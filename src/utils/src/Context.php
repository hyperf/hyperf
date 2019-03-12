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

use Swoole\Coroutine as SwCoroutine;

class Context
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function set(string $id, $value)
    {
        SwCoroutine::getContext()[static::getCoroutineId()][$id] = $value;
        return $value;
    }

    public static function get(string $id, $default = null)
    {
        return SwCoroutine::getContext()[static::getCoroutineId()][$id] ?? $default;
    }

    public static function has(string $id)
    {
        return isset(SwCoroutine::getContext()[static::getCoroutineId()][$id]);
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
        SwCoroutine::getContext()[$toCoroutineId] = SwCoroutine::getContext()[$fromCoroutineId];
    }

    public static function getContainer()
    {
        return SwCoroutine::getContext();
    }

    protected static function getCoroutineId()
    {
        return Coroutine::id();
    }
}
