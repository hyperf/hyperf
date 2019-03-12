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
        SwCoroutine::getContext()[$id] = $value;
        return $value;
    }

    public static function get(string $id, $default = null)
    {
        return SwCoroutine::getContext()[$id] ?? $default;
    }

    public static function has(string $id)
    {
        return isset(SwCoroutine::getContext()[$id]);
    }

    /**
     * Copy the context from a coroutine to current coroutine.
     */
    public static function copy(int $fromCoroutineId): void
    {
        /**
         * @var \ArrayObject $from
         * @var \ArrayObject $current
         */
        $from = SwCoroutine::getContext($fromCoroutineId);
        $current = SwCoroutine::getContext();
        $current->unserialize($from->serialize());
    }

    public static function getContainer()
    {
        return SwCoroutine::getContext();
    }

}
