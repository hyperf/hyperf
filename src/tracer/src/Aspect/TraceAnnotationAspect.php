<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ArroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\Annotation\Trace;
use Hyperf\Tracer\Tracing;

/**
 * @Aspect
 */
class TraceAnnotationAspect implements ArroundInterface
{
    public $classes = [];

    public $annotations = [
        Trace::class,
    ];

    /**
     * @var Tracing
     */
    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $source = $proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName;
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Trace $annotation */
        if ($annotation = $metadata->method[Trace::class] ?? null) {
            $name = $annotation->name;
        } else {
            $name = $source;
        }
        $span = $this->tracing->span($name);
        $span->tag('source', $source);
        $span->start();
        $result = $proceedingJoinPoint->process();
        $span->finish();
        return $result;
    }
}
