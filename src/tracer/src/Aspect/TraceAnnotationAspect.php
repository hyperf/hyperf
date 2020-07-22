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

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\Annotation\Trace;
use Hyperf\Tracer\SpanStarter;
use OpenTracing\Tracer;

/**
 * @Aspect
 */
class TraceAnnotationAspect implements AroundInterface
{
    use SpanStarter;

    public $classes = [];

    public $annotations = [
        Trace::class,
    ];

    /**
     * @var Tracer
     */
    private $tracer;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
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
            $tag = $annotation->tag;
        } else {
            $name = $source;
            $tag = 'source';
        }
        $span = $this->startSpan($name);
        $span->setTag($tag, $source);
        $result = $proceedingJoinPoint->process();
        $span->finish();
        return $result;
    }
}
