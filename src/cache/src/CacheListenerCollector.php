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
    /**
     * @var array
     */
    protected static $container = [];

    public static function setListener(string $listener, array $value)
    {
        static::$container[$listener] = $value;
    }

    public static function getListner(string $listener, $default = null)
    {
        return static::$container[$listener] ?? $default;
    }

    public static function clear(?string $className = null): void
    {
        if ($className) {
            foreach (static::$container as $listener => $value) {
                if (isset($value['className']) && $value['className'] === $className) {
                    unset(static::$container[$listener]);
                }
            }
        } else {
            static::$container = [];
        }
    }
}
