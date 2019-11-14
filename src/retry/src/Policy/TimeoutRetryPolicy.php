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

namespace Hyperf\Retry\Policy;

class TimeoutRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var float
     */
    private $timeout;

    public function __construct($timeout)
    {
        $this->timeout = $timeout;
    }

    public function canRetry(array &$retryContext): bool
    {
        if (microtime(true) < $retryContext['start_time'] + $this->timeout) {
            return true;
        }
        $retryContext['retry_exhausted'] = true;
        return false;
    }

    public function start(array $parentRetryContext = []): array
    {
        $parentRetryContext['start_time'] = microtime(true);
        return $parentRetryContext;
    }
}
