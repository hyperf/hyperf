<?php

namespace Hyperflex\Utils;

use Swoole\Coroutine as SwooleCoroutine;

class Coroutine
{

    /**
     * Coroutine releation map.
     *
     * @var array
     */
    private static $map = [];

    /**
     * Top level coroutine ID.
     *
     * @var int
     */
    private static $topCoroutineId = -1;

    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        $childCoroutineId = SwooleCoroutine::getuid();
        if ($childCoroutineId !== -1) {
            return $childCoroutineId;
        }
        return self::$topCoroutineId;
    }

    /**
     * Returns the top level coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function tid(): int
    {
        $id = self::id();
        return self::$map[$id] ?? $id;
    }

    /**
     * @param callable $callback
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public static function create(callable $callback): int
    {
        $tid = self::tid();
        $result = SwooleCoroutine::create(function () use ($callback, $tid) {
            $id = SwooleCoroutine::getuid();
            self::$map[$id] = $tid;
            call($callback);
        });
        return is_int($result) ? $result : -1;
    }

    /**
     * @param callable $callback
     */
    public static function defer(callable $callback): void
    {
        SwooleCoroutine::defer($callback);
    }

}