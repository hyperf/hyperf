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

    public static function has(string $annotation): bool
    {
        return isset(static::$container[$annotation]);
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
