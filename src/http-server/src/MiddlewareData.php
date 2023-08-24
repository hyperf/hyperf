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

use SplPriorityQueue;

class MiddlewareData
{
    public const DEFAULT_PRIORITY = 0;

    public function __construct(public string $middlewareClass, public int $priority = self::DEFAULT_PRIORITY)
    {
    }

    /**
     * @return string[]
     */
    public static function getPriorityMiddlewares(array $middlewares): array
    {
        $queue = new SplPriorityQueue();
        foreach ($middlewares as $middleware => $priority) {
            // - Hyperf\HttpServer\MiddlewareData Object
            // - Middleware::class
            // - Middleware::class => priority
            if ($priority instanceof MiddlewareData) {
                $middleware = $priority;
                $middlewareClass = $middleware->middlewareClass;
                $priority = $middleware->priority;
            } elseif (is_int($middleware)) {
                $middlewareClass = $priority;
                $priority = MiddlewareData::DEFAULT_PRIORITY;
            } else {
                $middlewareClass = $middleware;
            }

            $queue->insert($middlewareClass, $priority);
        }

        $middlewares = [];
        foreach ($queue as $item) {
            $middlewares[] = $item;
        }

        return array_unique($middlewares);
    }
}
