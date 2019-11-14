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

use Hyperf\Retry\CircuitBreakerState;

class CircuitBreakerRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var CircuitBreakerState
     */
    private $state;

    public function __construct(CircuitBreakerState $circuitBreakerState)
    {
        $this->state = $circuitBreakerState;
    }

    public function canRetry(array &$retryContext): bool
    {
        if (! $this->state->isOpen()) {
            return true;
        }
        return false;
    }

    public function end(array &$retryContext): bool
    {
        if (! isset($retryContext['retry_exhausted'])) {
            return false;
        }
        $this->state->open();
        return false;
    }
}
