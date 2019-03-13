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

namespace Hyperf\Di\Definition;

class PropertyHandlerManager
{
    /**
     * @var array
     */
    private static $container = [];

    public static function register(string $annotation, callable $callback)
    {
        static::$container[$annotation][] = $callback;
    }

    /**
     * @return callable[]
     */
    public static function get(string $annotation): array
    {
        return static::$container[$annotation];
    }

    public static function all(): array
    {
        return static::$container;
    }
}
