<?php

namespace Hyperf\Tracer\Aspect;


use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ArroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\Annotation\Trace;
use Hyperf\Tracer\Tracing;

/**
 * @Aspect()
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
        if (isset($metadata->method[Trace::class]['name'])) {
            $name = $metadata->method[Trace::class]['name'];
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