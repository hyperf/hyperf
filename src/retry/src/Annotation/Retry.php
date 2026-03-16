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
use Hyperf\Retry\Policy\BudgetRetryPolicy;
use Hyperf\Retry\Policy\ClassifierRetryPolicy;
use Hyperf\Retry\Policy\FallbackRetryPolicy;
use Hyperf\Retry\Policy\MaxAttemptsRetryPolicy;
use Hyperf\Retry\Policy\SleepRetryPolicy;
use Hyperf\Retry\RetryBudget;
use Hyperf\Retry\RetryBudgetInterface;
use Hyperf\Retry\SleepStrategyInterface;
use Throwable;

use function Hyperf\Support\make;

#[Attribute(Attribute::TARGET_METHOD)]
class Retry extends AbstractRetry
{
    /**
     * @param string[] $policies Array of retry policies. Think of these as stacked middlewares.
     * @param string $sleepStrategyClass the algorithm for retry intervals
     * @param int $maxAttempts max Attempts
     * @param array|RetryBudgetInterface $retryBudget Retry Budget. ttl: Seconds of token lifetime. minRetriesPerSec: Base retry token generation speed. percentCanRetry: Generate new token at this ratio of the request volume.
     * @param int $base Base time interval (ms) for each try. For backoff strategy this is the interval for the first try while for flat strategy this is the interval for every try.
     * @param callable|string $retryOnThrowablePredicate Configures a Predicate which evaluates if an exception should be retried. The Predicate must return true if the exception should be retried, otherwise it must return false.
     * @param callable|string $retryOnResultPredicate Configures a Predicate which evaluates if a result should be retried. The Predicate must return true if the result should be retried, otherwise it must return false.
     * @param array<string|Throwable> $retryThrowables Configures a list of Throwable classes that are recorded as a failure and thus are retried. Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions. Ignoring a Throwable has priority over retrying an exception.
     * @param array<string|Throwable> $ignoreThrowables Configures a list of error classes that are ignored and thus are not retried. Any exception matching or inheriting from one of the list will not be retried, even if marked via retryExceptions.
     * @param callable|string $fallback the fallback callable when all attempts exhausted
     */
    public function __construct(
        public array $policies = [
            FallbackRetryPolicy::class,
            ClassifierRetryPolicy::class,
            BudgetRetryPolicy::class,
            MaxAttemptsRetryPolicy::class,
            SleepRetryPolicy::class,
        ],
        public string $sleepStrategyClass = SleepStrategyInterface::class,
        public int $maxAttempts = 10,
        public array|RetryBudgetInterface $retryBudget = [
            'ttl' => 10,
            'minRetriesPerSec' => 1,
            'percentCanRetry' => 0.2,
        ],
        public int $base = 0,
        public mixed $retryOnThrowablePredicate = '',
        public mixed $retryOnResultPredicate = '',
        public array $retryThrowables = [Throwable::class],
        public array $ignoreThrowables = [],
        public mixed $fallback = ''
    ) {
        $this->retryBudget = make(RetryBudget::class, $this->retryBudget);
    }
}
