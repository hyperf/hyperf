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
use Hyperf\HttpServer\MiddlewareData;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware extends AbstractMultipleAnnotation
{
    public MiddlewareData $middlewareData;

    public function __construct(string $middleware = '', public int $priority = MiddlewareData::DEFAULT_PRIORITY)
    {
        $this->middlewareData = new MiddlewareData($middleware, $priority);
    }
}
