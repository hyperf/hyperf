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
namespace Hyperf\HttpServer;

class MiddlewareManager
{
    /**
     * @var array
     */
    public static $container = [];

    public static function addMiddleware(string $server, string $path, string $method, string $middleware): void
    {
        $method = strtoupper($method);
        static::$container[$server][$path][$method][] = $middleware;
    }

    public static function addMiddlewares(string $server, string $path, string $method, array $middlewares): void
    {
        $method = strtoupper($method);
        foreach ($middlewares as $middleware) {
            static::$container[$server][$path][$method][] = $middleware;
        }
    }

    public static function get(string $server, string $rule, string $method): array
    {
        $method = strtoupper($method);
        return static::$container[$server][$rule][$method] ?? [];
    }
}
