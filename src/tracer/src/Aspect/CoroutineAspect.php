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
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\TracerContext;
use OpenTracing\Span;
use Throwable;

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    public function __construct(protected SwitchManager $switchManager, protected SpanTagManager $spanTagManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switchManager->isEnable('coroutine')) {
            return $proceedingJoinPoint->process();
        }

        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $root = TracerContext::getRoot();

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $root) {
            try {
                if ($root instanceof Span) {
                    $tracer = TracerContext::getTracer();
                    $child = $tracer->startSpan('coroutine', [
                        'child_of' => $root->getContext(),
                    ]);
                    if ($this->spanTagManager->has('coroutine', 'id')) {
                        $child->setTag($this->spanTagManager->get('coroutine', 'id'), Co::id());
                    }
                    TracerContext::setRoot($child);
                    Co::defer(function () use ($child, $tracer) {
                        $child->finish();
                        $tracer->flush();
                    });
                }

                $callable();
            } catch (Throwable $e) {
                if (isset($child) && $this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($e)) {
                    $child->setTag('error', true);
                    $child->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
                }

                throw $e;
            }
        };

        return $proceedingJoinPoint->process();
    }
}
