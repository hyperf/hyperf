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
use stdClass;
use Throwable;

class CoroutineAspect extends AbstractAspect
{
    /** @var string[] */
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
            if (! $root instanceof Span) {
                $callable();
                return;
            }

            $tracer = TracerContext::getTracer();
            $child = $tracer->startSpan('coroutine', [
                'child_of' => $root->getContext(),
            ]);

            if ($this->spanTagManager->has('coroutine', 'id')) {
                $child->setTag(
                    $this->spanTagManager->get('coroutine', 'id'),
                    (string) Co::id()
                );
            }

            TracerContext::setRoot($child);

            $state = new stdClass();
            $state->finished = false;

            Co::defer(function () use ($child, $tracer, $state): void {
                /* @phpstan-ignore-next-line if.alwaysFalse */
                if ($state->finished) {
                    return;
                }
                $state->finished = true;
                $child->finish();
                $tracer->flush();
            });

            try {
                $callable();
            } catch (Throwable $e) {
                if (
                    $this->switchManager->isEnable('exception')
                    && ! $this->switchManager->isIgnoreException($e)
                ) {
                    $child->setTag('error', true);
                    $child->log([
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'stacktrace' => $e->getTraceAsString(),
                    ]);
                }
                throw $e;
            } finally {
                /* @phpstan-ignore-next-line if.alwaysFalse */
                if ($state->finished) {
                    return;
                }
                $state->finished = true;
                $child->finish();
                $tracer->flush();
            }
        };

        return $proceedingJoinPoint->process();
    }
}
