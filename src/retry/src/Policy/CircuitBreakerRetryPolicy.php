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

use Hyperf\Retry\CircuitBreakerState;
use Hyperf\Retry\RetryContext;

class CircuitBreakerRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    public function __construct(private CircuitBreakerState $circuitBreakerState)
    {
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        if (! $this->circuitBreakerState->isOpen()) {
            return true;
        }
        $retryContext['retryExhausted'] = true;
        return false;
    }

    public function end(RetryContext &$retryContext): bool
    {
        if (! isset($retryContext['retryExhausted'])) {
            return false;
        }
        $this->circuitBreakerState->open();
        return false;
    }
}
