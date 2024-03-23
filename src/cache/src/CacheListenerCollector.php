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

namespace Hyperf\Cache;

use Hyperf\Di\MetadataCollector;

class CacheListenerCollector extends MetadataCollector
{
    protected static array $container = [];

    public static function setListener(string $listener, array $value)
    {
        static::$container[$listener] = $value;
    }

    public static function getListener(string $listener, ?array $default = null)
    {
        return static::$container[$listener] ?? $default;
    }

    /**
     * @param null|string $key className
     */
    public static function clear(?string $key = null): void
    {
        if ($key) {
            foreach (static::$container as $listener => $value) {
                if (isset($value['className']) && $value['className'] === $key) {
                    unset(static::$container[$listener]);
                }
            }
        } else {
            static::$container = [];
        }
    }
}
