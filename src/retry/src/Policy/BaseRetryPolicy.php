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

namespace Hyperf\Retry\Policy;

use Hyperf\Retry\RetryContext;

abstract class BaseRetryPolicy
{
    public function canRetry(RetryContext &$retryContext): bool
    {
        return true;
    }

    public function beforeRetry(RetryContext &$retryContext): void
    {
    }

    public function start(RetryContext $parentRetryContext): RetryContext
    {
        return $parentRetryContext;
    }

    public function end(RetryContext &$retryContext): bool
    {
        return false;
    }
}
