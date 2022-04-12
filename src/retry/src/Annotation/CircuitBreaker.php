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

#[Attribute(Attribute::TARGET_METHOD)]
class CircuitBreaker extends AbstractRetry
{
    /**
     * Array of retry policies. Think of these as stacked middlewares.
     * @var string[]
     */
    public array $policies = [
        FallbackRetryPolicy::class,
        ClassifierRetryPolicy::class,
        CircuitBreakerRetryPolicy::class,
        MaxAttemptsRetryPolicy::class,
        SleepRetryPolicy::class,
    ];

    /**
     * The algorithm for retry intervals.
     */
    public string $sleepStrategyClass = SleepStrategyInterface::class;

    /**
     * Max Attempts.
     */
    public int $maxAttempts = 10;

    /**
     * Circuit-Breaker state
     * resetTimeout: After retry session fails, all future tries will be blocked in this period.
     */
    public array|CircuitBreakerState $circuitBreakerState = [
        'resetTimeout' => 10,
    ];

    /**
     * Base time interval (ms) for each try. For backoff strategy this is the interval for the first try
     * while for flat strategy this is the interval for every try.
     */
    public int $base = 0;

    /**
     * Configures a Predicate which evaluates if an exception should be retried.
     * The Predicate must return true if the exception should be retried, otherwise it must return false.
     *
     * @var callable|string
     */
    public mixed $retryOnThrowablePredicate = '';

    /**
     * Configures a Predicate which evaluates if a result should be retried.
     * The Predicate must return true if the result should be retried, otherwise it must return false.
     *
     * @var callable|string
     */
    public mixed $retryOnResultPredicate = '';

    /**
     * Configures a list of Throwable classes that are recorded as a failure and thus are retried.
     * Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions.
     *
     * Ignoring a Throwable has priority over retrying an exception.
     *
     * @var array<string|\Throwable>
     */
    public array $retryThrowables = [\Throwable::class];

    /**
     * Configures a list of error classes that are ignored and thus are not retried.
     * Any exception matching or inheriting from one of the list will not be retried, even if marked via retryExceptions.
     *
     * @var array<string|\Throwable>
     */
    public array $ignoreThrowables = [];

    /**
     * The fallback callable when all attempts exhausted.
     *
     * @var callable|string
     */
    public mixed $fallback = '';

    public function __construct(?array $policies = null, ?string $sleepStrategyClass = null, ?int $maxAttempts = null, array|CircuitBreakerState|null $circuitBreakerState = null, ?int $base = null, mixed $retryOnThrowablePredicate = null, mixed $retryOnResultPredicate = null, array $retryThrowables = null, array $ignoreThrowables = null, mixed $fallback = null)
    {
        $policies !== null && $this->policies = $policies;
        $sleepStrategyClass !== null && $this->sleepStrategyClass = $sleepStrategyClass;
        $maxAttempts !== null && $this->maxAttempts = $maxAttempts;
        $circuitBreakerState !== null && $this->circuitBreakerState = $circuitBreakerState;
        $base !== null && $this->base = $base;
        $retryOnThrowablePredicate !== null && $this->retryOnThrowablePredicate = $retryOnThrowablePredicate;
        $retryOnResultPredicate !== null && $this->retryOnResultPredicate = $retryOnResultPredicate;
        $retryThrowables !== null && $this->retryThrowables = $retryThrowables;
        $ignoreThrowables !== null && $this->ignoreThrowables = $ignoreThrowables;
        $fallback !== null && $this->fallback = $fallback;
    }

    public function toArray(): array
    {
        if (is_array($this->circuitBreakerState)) {
            $this->circuitBreakerState = make(CircuitBreakerState::class, $this->circuitBreakerState);
        }
        return parent::toArray();
    }
}
