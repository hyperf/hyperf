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
    /**
     * Array of retry policies. Think of these as stacked middlewares.
     * @var string[]
     */
    public $policies = [
        FallbackRetryPolicy::class,
        ClassifierRetryPolicy::class,
        BudgetRetryPolicy::class,
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
     * Retry Budget.
     * ttl: Seconds of token lifetime.
     * minRetriesPerSec: Base retry token generation speed.
     * percentCanRetry: Generate new token at this ratio of the request volume.
     *
     * @var array|RetryBudgetInterface
     */
    public $retryBudget = [
        'ttl' => 10,
        'minRetriesPerSec' => 1,
        'percentCanRetry' => 0.2,
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
     * @var array<string>
     */
    public $retryThrowables = [\Throwable::class];

    /**
     * Configures a list of error classes that are ignored and thus are not retried.
     * Any exception matching or inheriting from one of the list will not be retried, even if marked via retryExceptions.
     *
     * @var array<string>
     */
    public $ignoreThrowables = [];

    /**
     * The fallback callable when all attempts exhausted.
     *
     * @var callable|string
     */
    public $fallback = '';

    public function __construct(...$value)
    {
        parent::__construct(...$value);
        $this->retryBudget = make(RetryBudget::class, $this->retryBudget);
    }
}
