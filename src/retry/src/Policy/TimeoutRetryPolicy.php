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

class TimeoutRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    public function __construct(private float $timeout)
    {
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        if (microtime(true) < $retryContext['startTime'] + $this->timeout) {
            return true;
        }
        $retryContext['retryExhausted'] = true;
        return false;
    }

    public function start(RetryContext $parentRetryContext): RetryContext
    {
        $parentRetryContext['startTime'] = microtime(true);
        return $parentRetryContext;
    }
}
