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
namespace Hyperf\HttpServer\Router;

class RouteNameCollector
{
    protected static $routerNames = [];

    public static function add(string $server, string $name, string $route): void
    {
        static::$routerNames[$server][$name] = $route;
    }

    public static function get(string $server, string $name): ?string
    {
        return static::$routerNames[$server][$name] ?? null;
    }
}
