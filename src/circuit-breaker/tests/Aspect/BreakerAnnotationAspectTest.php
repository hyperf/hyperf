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

namespace HyperfTest\CircuitBreaker\Aspect;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\CircuitBreaker\Aspect\BreakerAnnotationAspect;
use Hyperf\CircuitBreaker\Handler\TimeoutHandler;
use Hyperf\Di\Aop\AnnotationMetadata;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use HyperfTest\CircuitBreaker\Stub\CircuitBreakerStub;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class BreakerAnnotationAspectTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testProcess()
    {
        $container = m::mock(ContainerInterface::class);
        $container->allows('has')->with(TimeoutHandler::class)->andReturn(false);
        $proceedingJoinPoint = m::mock(ProceedingJoinPoint::class);
        $proceedingJoinPoint->allows('process')->andReturn('foo');
        $proceedingJoinPoint
            ->allows('getAnnotationMetadata')
            ->andReturn(
                new AnnotationMetadata([], [CircuitBreaker::class => CircuitBreakerStub::makeCircuitBreaker()])
            );
        $instance = new BreakerAnnotationAspect($container);
        $this->assertSame($instance->process($proceedingJoinPoint), 'foo');
    }

    public function testProcessHandler()
    {
        $handler = m::mock(TimeoutHandler::class);
        $handler->allows('handle')->once()->andReturnUsing(fn (ProceedingJoinPoint $proceedingJoinPoint) => $proceedingJoinPoint->process());
        $container = m::mock(ContainerInterface::class);
        $container->allows('has')->once()->with(TimeoutHandler::class)->andReturn(true);
        $container->allows('get')->once()->with(TimeoutHandler::class)->andReturn($handler);
        $proceedingJoinPoint = m::mock(ProceedingJoinPoint::class);
        $proceedingJoinPoint->className = 'foo';
        $proceedingJoinPoint->methodName = 'bar';
        $proceedingJoinPoint->allows('process')->once()->andReturn('foo');
        $proceedingJoinPoint
            ->allows('getAnnotationMetadata')
            ->andReturn(
                new AnnotationMetadata([], [CircuitBreaker::class => CircuitBreakerStub::makeCircuitBreaker()])
            );
        $instance = new BreakerAnnotationAspect($container);
        $this->assertSame($instance->process($proceedingJoinPoint), 'foo');
    }
}
