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
use Hyperf\Retry\Annotation\Retry;
use Hyperf\Retry\RetryBudget;
use Hyperf\Retry\RetryBudgetInterface;
use Hyperf\Utils\Arr;

/**
 * @Aspect
 */
class RetryAnnotationAspect implements AroundInterface
{
    public $classes = [];

    public $annotations = [
        Retry::class,
    ];

    /**
     * @var array<string,RetryBudget>
     */
    private $budgets;

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $this->getAnnotations($proceedingJoinPoint);

        //Initialize Attempts
        $attempts = 1;

        //Initialize Strategy
        $strategy = make(
            $annotation->strategy,
            ['base' => $annotation->base]
        );

        //Initialize Retry Budget
        $budgetKey = $proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName;
        if (isset($this->budgets[$budgetKey])) {
            $budget = $this->budgets[$budgetKey];
        } else {
            $budget = make(RetryBudgetInterface::class, $annotation->retryBudget);
            $this->budgets[$budgetKey] = $budget;
        }
        $budget->produce();

        begin:

        $res = null; //fix phpstan
        try {
            $res = $proceedingJoinPoint->process();
        } catch (\Throwable $t) {
            if ($attempts >= $annotation->maxAttempts) {
                throw $t;
            }
            if (! $this->isRetriable($annotation, $t)) {
                throw $t;
            }
            ++$attempts;
            $strategy->sleep();
            if (! $budget->consume()) {
                throw $t;
            }
            goto begin;
        }

        if ($attempts >= $annotation->maxAttempts) {
            return $res;
        }

        if (! is_callable($annotation->retryOnResultPredicate)) {
            return $res;
        }

        $shouldRetry = call_user_func($annotation->retryOnResultPredicate, $res);

        if ($shouldRetry) {
            ++$attempts;
            $strategy->sleep();
            if (! $budget->consume()) {
                return $res;
            }
            goto begin;
        }

        return $res;
    }

    public function getAnnotations(ProceedingJoinPoint $proceedingJoinPoint): Retry
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        return $metadata->method[Retry::class] ?? new Retry();
    }

    private function in(\Throwable $t, array $arr): bool
    {
        return Arr::first(
            $arr,
            function ($v) use ($t) {
                return $t instanceof $v;
            }
        ) ? true : false;
    }

    private function isRetriable(Retry $annotation, \Throwable $t): bool
    {
        if ($this->in($t, $annotation->ignoreThrowables)) {
            return false;
        }

        if ($this->in($t, $annotation->retryThrowables)) {
            return true;
        }

        if (! is_callable($annotation->retryOnThrowablePredicate)) {
            return false;
        }

        if (call_user_func($annotation->retryOnThrowablePredicate, $t)) {
            return true;
        }

        return false;
    }
}
