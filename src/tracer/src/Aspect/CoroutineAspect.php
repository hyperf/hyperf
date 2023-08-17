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
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Tracer\TracerContext;
use OpenTracing\Span;
use Throwable;

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $root = TracerContext::getRoot();

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $root) {
            try {
                if ($root instanceof Span) {
                    $tracer = TracerContext::getTracer();
                    $child = $tracer->startSpan('coroutine', [
                        'child_of' => $root->getContext(),
                    ]);
                    $child->setTag('coroutine.id', Co::id());
                    TracerContext::setRoot($child);
                    Co::defer(function () use ($child, $tracer) {
                        $child->finish();
                        $tracer->flush();
                    });
                }

                $callable();
            } catch (Throwable $e) {
                if (isset($child)) {
                    $child->setTag('error', true);
                    $child->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
                }

                throw $e;
            }
        };

        return $proceedingJoinPoint->process();
    }
}
