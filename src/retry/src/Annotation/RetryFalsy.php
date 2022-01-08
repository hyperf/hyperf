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

#[Attribute(Attribute::TARGET_METHOD)]
class RetryFalsy extends Retry
{
    /**
     * Configures a list of Throwable classes that are recorded as a failure and thus are retried.
     * Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions.
     *
     * Ignoring a Throwable has priority over retrying an exception.
     *
     * @var array<string>
     */
    public array $retryThrowables = [];

    /**
     * Configures a Predicate which evaluates if a result should be retried.
     * The Predicate must return true if the result should be retried, otherwise it must return false.
     *
     * @var callable|string
     */
    public mixed $retryOnResultPredicate = [self::class, 'isFalsy'];

    public static function isFalsy($result)
    {
        return ! $result;
    }
}
