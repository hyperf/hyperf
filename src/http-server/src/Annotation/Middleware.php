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
use Hyperf\Di\Annotation\AbstractMultipleAnnotation;
use Hyperf\HttpServer\PriorityMiddleware;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware extends AbstractMultipleAnnotation
{
    public PriorityMiddleware $priorityMiddleware;

    public function __construct(public string $middleware = '', public int $priority = PriorityMiddleware::DEFAULT_PRIORITY)
    {
        $this->priorityMiddleware = new PriorityMiddleware($middleware, $priority);
    }
}
