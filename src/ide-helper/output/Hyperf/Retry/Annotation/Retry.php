<?php

declare (strict_types=1);
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
use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Retry\Policy\BudgetRetryPolicy;
use Hyperf\Retry\Policy\ClassifierRetryPolicy;
use Hyperf\Retry\Policy\FallbackRetryPolicy;
use Hyperf\Retry\Policy\MaxAttemptsRetryPolicy;
use Hyperf\Retry\Policy\SleepRetryPolicy;
use Hyperf\Retry\RetryBudget;
use Hyperf\Retry\RetryBudgetInterface;
use Hyperf\Retry\SleepStrategyInterface;
/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Retry extends AbstractRetry
{
    public function __construct($policies, $sleepStrategyClass, $maxAttempts, $retryBudget, $base, $retryOnThrowablePredicate, $retryOnResultPredicate, $retryThrowables, $ignoreThrowables, $fallback)
    {
    }
}