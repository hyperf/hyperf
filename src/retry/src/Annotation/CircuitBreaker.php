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
    /**
     * Array of retry policies. Think of these as stacked middlewares.
     * @var string[]
     */
    public $policies = [
        FallbackRetryPolicy::class,
        ClassifierRetryPolicy::class,
        CircuitBreakerRetryPolicy::class,
        MaxAttemptsRetryPolicy::class,
        SleepRetryPolicy::class,
    ];

    /**
     * The algorithm for retry intervals.
     * @var string
     */
    public $sleepStrategyClass = SleepStrategyInterface::class;

    /**
     * Max Attampts.
     * @var int
     */
    public $maxAttempts = 10;

    /**
     * Circuit-Breaker state
     * resetTimeout: After retry session fails, all future tries will be blocked in this period.
     *
     * @var array|CircuitBreakerState
     */
    public $circuitBreakerState = [
        'resetTimeout' => 10,
    ];

    /**
     * Base time inteval (ms) for each try. For backoff strategy this is the interval for the first try
     * while for flat strategy this is the interval for every try.
     * @var int
     */
    public $base = 0;

    /**
     * Configures a Predicate which evaluates if an exception should be retried.
     * The Predicate must return true if the exception should be retried, otherwise it must return false.
     *
     * @var callable|string
     */
    public $retryOnThrowablePredicate = '';

    /**
     * Configures a Predicate which evaluates if an result should be retried.
     * The Predicate must return true if the result should be retried, otherwise it must return false.
     *
     * @var callable|string
     */
    public $retryOnResultPredicate = '';

    /**
     * Configures a list of Throwable classes that are recorded as a failure and thus are retried.
     * Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions.
     *
     * Ignoring an Throwable has priority over retrying an exception.
     *
     * @var array<string|\Throwable>
     */
    public $retryThrowables = [\Throwable::class];

    /**
     * Configures a list of error classes that are ignored and thus are not retried.
     * Any exception matching or inheriting from one of the list will not be retried, even if marked via retryExceptions.
     *
     * @var array<string|\Throwable>
     */
    public $ignoreThrowables = [];

    /**
     * The fallback callable when all attempts exhausted.
     *
     * @var callable|string
     */
    public $fallback = '';

    public function toArray(): array
    {
        if (is_array($this->circuitBreakerState)) {
            $this->circuitBreakerState = make(CircuitBreakerState::class, $this->circuitBreakerState);
        }
        return parent::toArray();
    }
}
