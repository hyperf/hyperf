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
use Hyperf\Tracer\Annotation\Trace;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SwitchManager;
use Throwable;

class TraceAnnotationAspect extends AbstractAspect
{
    use SpanStarter;

    public array $annotations = [
        Trace::class,
    ];

    public function __construct(private SwitchManager $switchManager)
    {
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
        try {
            $result = $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            if ($this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($e)) {
                $span->setTag('error', true);
                $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
            }
            throw $e;
        } finally {
            $span->finish();
        }
        return $result;
    }
}
