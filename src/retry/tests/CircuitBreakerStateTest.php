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

namespace HyperfTest\Retry;

use Hyperf\Retry\CircuitBreakerState;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CircuitBreakerStateTest extends TestCase
{
    public function testCircuitBreakerState()
    {
        $state = new CircuitBreakerState(
            0.001
        );
        $this->assertFalse($state->isOpen());
        $state->open();
        $this->assertTrue($state->isOpen()); // open
        usleep(1000);
        $this->assertFalse($state->isOpen());
    }
}
