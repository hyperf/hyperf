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
use Throwable;

#[Attribute(Attribute::TARGET_METHOD)]
class RetryThrowable extends Retry
{
    /**
     * @param array<string|Throwable> $retryThrowables Configures a list of Throwable classes that are recorded as a failure and thus are retried. Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions. Ignoring a Throwable has priority over retrying an exception.
     */
    public function __construct(public array $retryThrowables = [Throwable::class])
    {
    }
}
