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

namespace Hyperf\Retry\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Retry\Annotation\AbstractRetry;
use Hyperf\Retry\Annotation\Retry;
use Hyperf\Retry\Policy\HybridRetryPolicy;
use Throwable;

use function Hyperf\Support\make;

class RetryAnnotationAspect extends AbstractAspect
{
    public array $annotations = [
        AbstractRetry::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $this->getAnnotations($proceedingJoinPoint);
        $policy = $this->makePolicy($annotation);
        $context = $policy->start();
        $context['proceedingJoinPoint'] = $proceedingJoinPoint;
        $context['pipe'] = $proceedingJoinPoint->pipe;
        if (! $policy->canRetry($context)) {
            goto end;
        }

        attempt: // Make an attempt to (re)try.

        $context['lastResult'] = $context['lastThrowable'] = null;
        try {
            $context['lastResult'] = $proceedingJoinPoint->process();
        } catch (Throwable $throwable) {
            $context['lastThrowable'] = $throwable;
        }
        if ($policy->canRetry($context)) {
            $policy->beforeRetry($context);
            $proceedingJoinPoint->pipe = $context['pipe'];
            goto attempt;
        }

        end: // Break out of retry

        $policy->end($context);
        if ($context['lastThrowable'] !== null) {
            throw $context['lastThrowable'];
        }
        return $context['lastResult'];
    }

    public function getAnnotations(ProceedingJoinPoint $proceedingJoinPoint): AbstractRetry
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        return $metadata->method[AbstractRetry::class] ?? new Retry();
    }

    private function makePolicy(AbstractRetry $annotation): HybridRetryPolicy
    {
        $policies = [];
        foreach ($annotation->policies as $policy) {
            $policies[] = make($policy, $annotation->toArray());
        }

        return new HybridRetryPolicy(...$policies);
    }
}
