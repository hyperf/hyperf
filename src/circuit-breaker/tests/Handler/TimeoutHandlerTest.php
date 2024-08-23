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

namespace HyperfTest\CircuitBreaker\Handler;

use Hyperf\CircuitBreaker\CircuitBreaker;
use Hyperf\CircuitBreaker\CircuitBreakerFactory;
use Hyperf\CircuitBreaker\Handler\TimeoutHandler;
use Hyperf\CircuitBreaker\LoggerInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use HyperfTest\CircuitBreaker\Stub\CircuitBreakerStub;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class TimeoutHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testProcess()
    {
        $container = m::mock(ContainerInterface::class);
        $logger = m::mock(LoggerInterface::class);
        $container->allows('has')->once()->with(LoggerInterface::class)->andReturn(true);
        $container->allows('get')->once()->with(LoggerInterface::class)->andReturn($logger);
        $factory = m::mock(CircuitBreakerFactory::class);
        $instance = new CircuitBreaker($container, 'Foo::bar');
        $factory->allows('get')->with('Foo::bar')->andReturn($instance);
        $container->allows('get')->once()->with(CircuitBreakerFactory::class)->andReturn($factory);
        $handler = new TimeoutHandler($container);
        $proceedingJoinPoint = m::mock(ProceedingJoinPoint::class);
        $proceedingJoinPoint->className = 'Foo';
        $proceedingJoinPoint->methodName = 'bar';
        $proceedingJoinPoint->allows('process')
            ->times(2)->andReturnUsing(fn () => 'foo', function () {
                sleep(2);
                return 'sleep2';
            });
        $annotation = CircuitBreakerStub::makeCircuitBreaker();
        $logger->allows('debug')->times(4);
        $this->assertSame('foo', $handler->handle($proceedingJoinPoint, $annotation));
        $this->assertSame(1, $instance->getSuccessCounter());
        $this->assertSame(0, $instance->getFailCounter());
        $this->assertSame('sleep2', $handler->handle($proceedingJoinPoint, $annotation));
        $this->assertSame(1, $instance->getFailCounter());
        $this->assertSame(1, $instance->getSuccessCounter());
        $state = $instance->state();
        $this->assertTrue($state->isClose());
    }
}
