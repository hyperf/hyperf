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

use Laminas\Stdlib\SplPriorityQueue;

class PriorityMiddleware
{
    public const DEFAULT_PRIORITY = 0;

    public function __construct(public string $middleware, public int $priority = self::DEFAULT_PRIORITY)
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
            if ($priority instanceof PriorityMiddleware) {
                [$middleware, $priority] = [$priority->middleware, $priority->priority];
            } elseif (is_int($middleware)) {
                [$middleware, $priority] = [$priority, PriorityMiddleware::DEFAULT_PRIORITY];
            }

            $queue->insert($middleware, $priority);
        }

        return array_unique($queue->toArray());
    }
}
