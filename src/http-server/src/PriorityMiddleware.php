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
            if ($priority instanceof PriorityMiddleware) {// - Hyperf\HttpServer\MiddlewareData Object
                [$middleware, $priority] = [$priority->middleware, $priority->priority];
            } elseif (is_int($middleware)) {// - Middleware::class
                [$middleware, $priority] = [$priority, PriorityMiddleware::DEFAULT_PRIORITY];
            }
            // - Default Middleware::class => priority

            $queue->insert($middleware, $priority);
        }

        return array_unique($queue->toArray());
    }
}
