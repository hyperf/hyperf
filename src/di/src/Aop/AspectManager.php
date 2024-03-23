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

namespace Hyperf\Di\Aop;

class AspectManager
{
    protected static array $container = [];

    public static function get($class, $method)
    {
        return static::$container[$class][$method] ?? [];
    }

    public static function has($class, $method)
    {
        return isset(static::$container[$class][$method]);
    }

    public static function set($class, $method, $value)
    {
        static::$container[$class][$method] = $value;
    }

    public static function insert($class, $method, $value)
    {
        static::$container[$class][$method][] = $value;
    }
}
