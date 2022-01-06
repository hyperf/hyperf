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
#[Attribute(Attribute::TARGET_METHOD)]
class CircuitBreaker extends AbstractRetry
{
    public function __construct(array $policies = ['Hyperf\\Retry\\Policy\\FallbackRetryPolicy', 'Hyperf\\Retry\\Policy\\ClassifierRetryPolicy', 'Hyperf\\Retry\\Policy\\CircuitBreakerRetryPolicy', 'Hyperf\\Retry\\Policy\\MaxAttemptsRetryPolicy', 'Hyperf\\Retry\\Policy\\SleepRetryPolicy'], string $sleepStrategyClass = 'Hyperf\\Retry\\SleepStrategyInterface', int $maxAttempts = 10, \Hyperf\Retry\CircuitBreakerState|array $circuitBreakerState = [10], int $base = 0, mixed $retryOnThrowablePredicate = '', mixed $retryOnResultPredicate = '', array $retryThrowables = ['Throwable'], array $ignoreThrowables = [], mixed $fallback = '')
    {
    }
}