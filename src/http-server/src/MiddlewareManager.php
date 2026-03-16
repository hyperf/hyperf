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

use Hyperf\Stdlib\SplPriorityQueue;

class MiddlewareManager
{
    public static array $container = [];

    public static function addMiddleware(string $server, string $path, string $method, string $middleware): void
    {
        $method = strtoupper($method);
        static::$container[$server][$path][$method][] = $middleware;
    }

    public static function addMiddlewares(string $server, string $path, string $method, array $middlewares): void
    {
        $method = strtoupper($method);
        foreach ($middlewares as $middleware => $priority) {
            if ($priority instanceof PriorityMiddleware) {
                static::$container[$server][$path][$method][] = $priority;
                continue;
            }

            if (is_int($priority)) {
                static::$container[$server][$path][$method][] = new PriorityMiddleware($middleware, $priority);
                continue;
            }

            $middleware = $priority;
            static::$container[$server][$path][$method][] = $middleware;
        }
    }

    public static function get(string $server, string $rule, string $method): array
    {
        $method = strtoupper($method);
        if (isset(static::$container[$server][$rule][$method])) {
            return static::$container[$server][$rule][$method];
        }

        // For HEAD requests, attempt fallback to GET
        // keep the same with FastRoute\Dispatcher\RegexBasedAbstract::dispatch
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        return static::$container[$server][$rule][$method] ?? [];
    }

    /**
     * @return string[]
     */
    public static function sortMiddlewares(array $middlewares): array
    {
        $queue = new SplPriorityQueue();
        foreach ($middlewares as $middleware => $priority) {
            if ($priority instanceof PriorityMiddleware) {
                // int => Hyperf\HttpServer\MiddlewareData Object
                [$middleware, $priority] = [$priority->middleware, $priority->priority];
            } elseif (is_int($middleware)) {
                // int => Middleware::class
                [$middleware, $priority] = [$priority, PriorityMiddleware::DEFAULT_PRIORITY];
            }

            // Default Middleware::class => priority
            $queue->insert($middleware, $priority);
        }

        return array_unique($queue->toArray());
    }
}
