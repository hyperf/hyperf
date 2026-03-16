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

namespace Hyperf\Tracer\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\TracerContext;
use Zipkin\Propagation\TraceContext;

class CreateTraceContextAspect extends AbstractAspect
{
    public array $classes = [
        TraceContext::class . '::create',
        TraceContext::class . '::create*',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $traceContext = $proceedingJoinPoint->process();
        if ($traceContext instanceof TraceContext) {
            TracerContext::setTraceId($traceContext->getTraceId());
        }
        return $traceContext;
    }
}
