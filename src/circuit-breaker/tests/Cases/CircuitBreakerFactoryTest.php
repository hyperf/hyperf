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

namespace HyperfTest\CircuitBreaker\Cases;

use Hyperf\CircuitBreaker\CircuitBreakerFactory;
use Hyperf\CircuitBreaker\CircuitBreakerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CircuitBreakerFactoryTest extends TestCase
{
    public function testGetReturnsNullForNonExistentBreaker()
    {
        $factory = new CircuitBreakerFactory();
        $this->assertNull($factory->get('non_existent'));
    }

    public function testGetReturnsBreakerIfExists()
    {
        $factory = new CircuitBreakerFactory();
        $breaker = $this->createMock(CircuitBreakerInterface::class);
        $factory->set('existent', $breaker);
        $this->assertSame($breaker, $factory->get('existent'));
    }

    public function testHasReturnsFalseForNonExistentBreaker()
    {
        $factory = new CircuitBreakerFactory();
        $this->assertFalse($factory->has('non_existent'));
    }

    public function testHasReturnsTrueForExistentBreaker()
    {
        $factory = new CircuitBreakerFactory();
        $breaker = $this->createMock(CircuitBreakerInterface::class);
        $factory->set('existent', $breaker);
        $this->assertTrue($factory->has('existent'));
    }

    public function testSetStoresAndReturnsBreaker()
    {
        $factory = new CircuitBreakerFactory();
        $breaker = $this->createMock(CircuitBreakerInterface::class);
        $this->assertSame($breaker, $factory->set('new_breaker', $breaker));
        $this->assertSame($breaker, $factory->get('new_breaker'));
    }
}
