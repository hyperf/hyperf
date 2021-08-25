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

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CircuitBreakerTest extends TestCase
{
    public function testAttributeCollect()
    {
        if (PHP_VERSION_ID >= 80000) {
            $breaker = new CircuitBreaker(timeout: 1);
            $this->assertSame(['timeout' => 1], $breaker->value);
        }
        $breaker = new CircuitBreaker(['timeout' => 1]);
        $this->assertSame(['timeout' => 1], $breaker->value);
    }
}
