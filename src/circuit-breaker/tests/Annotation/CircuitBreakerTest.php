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

namespace HyperfTest\CircuitBreaker\Annotation;

use Hyperf\CircuitBreaker\Handler\TimeoutHandler;
use HyperfTest\CircuitBreaker\Stub\CircuitBreakerStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CircuitBreakerTest extends TestCase
{
    public function testAttributeCollect()
    {
        $breaker = CircuitBreakerStub::makeCircuitBreaker();
        $this->assertSame(['timeout' => 1], $breaker->options);
    }

    public function testAttributeDefault()
    {
        $breaker = CircuitBreakerStub::makeCircuitBreaker();
        $this->assertSame(10.0, $breaker->duration);
        $this->assertSame(10, $breaker->successCounter);
        $this->assertSame(10, $breaker->failCounter);
        $this->assertSame([], $breaker->fallback);
        $this->assertSame(TimeoutHandler::class, $breaker->handler);
        $this->assertSame(['timeout' => 1], $breaker->options);
    }
}
