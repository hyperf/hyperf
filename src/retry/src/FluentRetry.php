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

namespace Hyperf\Retry;

use BadMethodCallException;
use Closure;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Retry\Policy\ClassifierRetryPolicy;
use Hyperf\Retry\Policy\ExpressionRetryPolicy;
use Hyperf\Retry\Policy\FallbackRetryPolicy;
use Hyperf\Retry\Policy\HybridRetryPolicy;
use Hyperf\Retry\Policy\MaxAttemptsRetryPolicy;
use Hyperf\Retry\Policy\RetryPolicyInterface;
use Hyperf\Retry\Policy\SleepRetryPolicy;
use Hyperf\Retry\Policy\TimeoutRetryPolicy;
use Throwable;

class FluentRetry
{
    /**
     * @var RetryPolicyInterface[]
     */
    protected array $policies = [];

    /**
     * @var callable
     */
    protected mixed $callable;

    public function with(RetryPolicyInterface ...$policies): FluentRetry
    {
        $this->policies = $policies;
        return $this;
    }

    public function when(callable $when): FluentRetry
    {
        $this->policies[] = new ExpressionRetryPolicy($when);
        return $this;
    }

    public function whenReturns($when): FluentRetry
    {
        $this->policies[] = new ClassifierRetryPolicy([], [], null, fn ($r) => $r === $when);
        return $this;
    }

    public function whenThrows(string $when = Throwable::class): FluentRetry
    {
        $this->policies[] = new ClassifierRetryPolicy([], [$when]);
        return $this;
    }

    public function max(int $times): FluentRetry
    {
        $this->policies[] = new MaxAttemptsRetryPolicy($times);
        return $this;
    }

    public function inSeconds(float $seconds): FluentRetry
    {
        $this->policies[] = new TimeoutRetryPolicy($seconds);
        return $this;
    }

    public function fallback(callable $fallback): FluentRetry
    {
        $this->policies[] = new FallbackRetryPolicy($fallback);
        return $this;
    }

    public function sleep(int $base): FluentRetry
    {
        $this->policies[] = new SleepRetryPolicy($base, FlatStrategy::class);
        return $this;
    }

    public function backoff(int $base): FluentRetry
    {
        $this->policies[] = new SleepRetryPolicy($base, BackoffStrategy::class);
        return $this;
    }

    public function call(callable $callable)
    {
        if (empty($this->policies)) {
            throw new BadMethodCallException('Please specify at least one policy before call');
        }
        $policy = new HybridRetryPolicy(...$this->policies);

        $context = $policy->start();
        // Fake join point for compatibility with Aspect;
        if (class_exists(ProceedingJoinPoint::class)) {
            $context['proceedingJoinPoint'] = new ProceedingJoinPoint(
                Closure::fromCallable($callable),
                '',
                '',
                []
            );
        }

        if (! $policy->canRetry($context)) {
            goto end;
        }

        attempt: // Make an attempt to (re)try.

        $context['lastResult'] = $context['lastThrowable'] = null;
        try {
            $context['lastResult'] = $callable();
        } catch (Throwable $throwable) {
            $context['lastThrowable'] = $throwable;
        }
        if ($policy->canRetry($context)) {
            $policy->beforeRetry($context);
            goto attempt;
        }

        end: // Break out of retry

        $policy->end($context);
        if ($context['lastThrowable'] instanceof Throwable) {
            throw $context['lastThrowable'];
        }
        return $context['lastResult'];
    }
}
