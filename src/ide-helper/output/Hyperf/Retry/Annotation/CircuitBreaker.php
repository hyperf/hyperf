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
use Hyperf\Retry\CircuitBreakerState;
use Hyperf\Retry\Policy\CircuitBreakerRetryPolicy;
use Hyperf\Retry\Policy\ClassifierRetryPolicy;
use Hyperf\Retry\Policy\FallbackRetryPolicy;
use Hyperf\Retry\Policy\MaxAttemptsRetryPolicy;
use Hyperf\Retry\Policy\SleepRetryPolicy;
use Hyperf\Retry\SleepStrategyInterface;
/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class CircuitBreaker extends AbstractRetry
{
    public function __construct($policies, $sleepStrategyClass, $maxAttempts, $circuitBreakerState, $base, $retryOnThrowablePredicate, $retryOnResultPredicate, $retryThrowables, $ignoreThrowables, $fallback)
    {
    }
}