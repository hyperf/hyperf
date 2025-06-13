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

namespace Hyperf\HttpServer\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\HttpServer\PriorityMiddleware;

#[Attribute]
class Middlewares extends AbstractAnnotation
{
    /**
     * @var Middleware[]
     */
    public array $middlewares = [];

    public function __construct(array $middlewares = [])
    {
        foreach ($middlewares as $middleware => $priority) {
            if (is_int($middleware)) {
                [$middleware, $priority] = [$priority, PriorityMiddleware::DEFAULT_PRIORITY];
            }

            $this->middlewares[] = $middleware instanceof Middlewares ? $middleware->middlewares : new Middleware((string) $middleware, (int) $priority);
        }
    }
}
