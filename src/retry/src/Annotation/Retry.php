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
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Retry\StrategyInterface;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Retry extends AbstractAnnotation
{
    /**
     * The algorithm for retry intervals.
     * @var string
     */
    public $strategy = StrategyInterface::class;

    /**
     * Max Attampts.
     * @var float|int
     */
    public $maxAttempts = INF;

    /**
     * Retry Budget.
     * @var array
     */
    public $retryBudget = [
        'ttl' => 10,
        'minRetriesPerSec' => 10,
        'percentCanRetry' => 0.2,
    ];

    /**
     * Base time inteval (ms) for each try.
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

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, self::class, $this);
    }
}
