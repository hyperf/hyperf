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

namespace Hyperf\Retry\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\StrategyInterface\BackoffStrategy;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RetryAnnotation extends AbstractAnnotation
{
    /**
     * The algorithm for retry intervals
     * @var string
     */
    public $strategy = BackoffStrategy::class;

    /**
     * Max Attampts
     * @var int
     */
    public $maxAttampts = INF;

    /**
     * Base time inteval (ms) for each try.
     * @var int
     */
    public $base = 1;

    /**
     * Configures a Predicate which evaluates if an exception should be retried.
     * The Predicate must return true if the exception should be retried, otherwise it must return false.
     *
     * @var Callable|string
     */
    public $retryOnThrowablePredicate = '';

    /**
     * Configures a Predicate which evaluates if an result should be retried.
     * The Predicate must return true if the result should be retried, otherwise it must return false.
     *
     * @var Callable|string
     */
    public $retryOnResultPredicate = '';

    /**
     * Configures a list of Throwable classes that are recorded as a failure and thus are retried.
     * Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions.
     *
     * Ignoring an Throwable has priority over retrying an exception.
     *
     * @var array<Throwable|string>
     */
    public $retryThrowables = [\Throwable::class];

    /**
     * Configures a list of error classes that are ignored and thus are not retried.
     * Any exception matching or inheriting from one of the list will not be retried, even if marked via retryExceptions.
     *
     * @var array<Throwable|string>
     */
    public $ignoreThrowables = [];
}
