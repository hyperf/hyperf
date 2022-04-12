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
namespace Hyperf\Retry;

class CircuitBreakerState
{
    /**
     * @param float $resetTimeout timeout to reset CircuitBreaker back to close
     * @param float|null $openTime Circuit Breaker State. A float value means open; null means close.
     */
    public function __construct(public float $resetTimeout, public ?float $openTime = null)
    {
    }

    public function isOpen(): bool
    {
        if ($this->openTime === null) {
            return false;
        }
        $now = microtime(true);
        if ($now > $this->openTime + $this->resetTimeout) {
            $this->openTime = null; // close the circuit
            return false;
        }
        return true;
    }

    public function open()
    {
        $this->openTime = microtime(true);
    }
}
