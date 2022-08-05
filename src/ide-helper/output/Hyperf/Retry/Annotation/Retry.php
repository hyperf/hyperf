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
namespace Hyperf\Retry\Annotation;

use Attribute;
use Hyperf\Retry\RetryBudgetInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class Retry extends AbstractRetry
{
    public function __construct(array $policies = [\Hyperf\Retry\Policy\FallbackRetryPolicy::class, \Hyperf\Retry\Policy\ClassifierRetryPolicy::class, \Hyperf\Retry\Policy\BudgetRetryPolicy::class, \Hyperf\Retry\Policy\MaxAttemptsRetryPolicy::class, \Hyperf\Retry\Policy\SleepRetryPolicy::class], string $sleepStrategyClass = \Hyperf\Retry\SleepStrategyInterface::class, int $maxAttempts = 10, RetryBudgetInterface|array $retryBudget = [10, 1, 0.2], int $base = 0, mixed $retryOnThrowablePredicate = '', mixed $retryOnResultPredicate = '', array $retryThrowables = [\Throwable::class], array $ignoreThrowables = [], mixed $fallback = '')
    {
    }
}
