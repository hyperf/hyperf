<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Retry\Aspect;

use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Retry\Annotation\AbstractRetry;
use Hyperf\Retry\Annotation\Retry;
use Hyperf\Retry\Policy\HybridRetryPolicy;

/**
 * @Aspect
 */
class RetryAnnotationAspect implements AroundInterface
{
    public $classes = [];

    public $annotations = [
        Retry::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $this->getAnnotations($proceedingJoinPoint);
        $policy = $this->makePolicy($annotation);
        $context = $policy->start();
        $context['proceeding_join_point'] = $proceedingJoinPoint;
        if (! $policy->canRetry($context)) {
            goto end;
        }

        attempt: // Make an attempt to (re)try.

        $context['last_result'] = $context['last_throwable'] = null;
        try {
            $context['last_result'] = $proceedingJoinPoint->process();
        } catch (\Throwable $throwable) {
            $context['last_throwable'] = $throwable;
        }
        if ($policy->canRetry($context)) {
            $policy->beforeRetry($context);
            goto attempt;
        }

        end: // Break out of retry

        $policy->end($context);
        if ($context['last_throwable'] !== null) {
            throw $context['last_throwable'];
        }
        return $context['last_result'];
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
            $policies[] = make($policy, (array) $annotation);
        }

        return new HybridRetryPolicy(...$policies);
    }
}
