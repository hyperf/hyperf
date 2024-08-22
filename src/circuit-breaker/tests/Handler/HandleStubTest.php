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

use Exception;
use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\CircuitBreaker\Attempt;
use Hyperf\CircuitBreaker\CircuitBreakerFactory;
use Hyperf\CircuitBreaker\Exception\BadFallbackException;
use Hyperf\CircuitBreaker\LoggerInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use HyperfTest\CircuitBreaker\Stub\HandleStub;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class HandleStubTest extends TestCase
{
    public function testHandleReturnsProcessedResult()
    {
        $container = m::mock(ContainerInterface::class);
        $proceedingJoinPoint = m::mock(ProceedingJoinPoint::class);
        $annotation = new CircuitBreaker();
        $breaker = new \Hyperf\CircuitBreaker\CircuitBreaker($container, 'TestClass::testMethod');
        $factory = m::mock(CircuitBreakerFactory::class);
        $logger = m::mock(LoggerInterface::class);
        $logger->allows('debug')->once();
        $container->allows('get')->once()->with(CircuitBreakerFactory::class)->andReturn($factory);
        $container->allows('has')->once()->with(LoggerInterface::class)->andReturn(true);
        $container->allows('get')->once()->with(LoggerInterface::class)->andReturn($logger);
        $factory->allows('get')->with('TestClass::testMethod')->andReturn($breaker);
        $proceedingJoinPoint->className = 'TestClass';
        $proceedingJoinPoint->methodName = 'testMethod';

        $handler = new HandleStub($container);
        $result = $handler->handle($proceedingJoinPoint, $annotation);

        $this->assertEquals('processed', $result);
    }

    public function testHandleFallbacksWhenStateIsOpen()
    {
        $container = m::mock(ContainerInterface::class);

        $container->allows('get')->once()->with(Attempt::class)->andReturn(new Attempt());
        $proceedingJoinPoint = m::mock(ProceedingJoinPoint::class);
        $annotation = new CircuitBreaker();
        $breaker = new \Hyperf\CircuitBreaker\CircuitBreaker($container, 'TestClass::testMethod');
        $factory = m::mock(CircuitBreakerFactory::class);
        $logger = m::mock(LoggerInterface::class);
        $logger->allows('debug')->once();
        $container->allows('get')->once()->with(CircuitBreakerFactory::class)->andReturn($factory);
        $container->allows('has')->once()->with(LoggerInterface::class)->andReturn(true);
        $container->allows('get')->once()->with(LoggerInterface::class)->andReturn($logger);
        $factory->allows('get')->with('TestClass::testMethod')->andReturn($breaker);
        $proceedingJoinPoint->className = 'TestClass';
        $proceedingJoinPoint->methodName = 'testMethod';
        $handler = new HandleStub($container);
        $result = $handler->handle($proceedingJoinPoint, $annotation);

        $this->assertSame($result, 'processed');
    }

    public function testHandleAttemptsCallWhenStateIsHalfOpen()
    {
        $container = m::mock(ContainerInterface::class);

        $container->allows('get')->once()->with(Attempt::class)->andReturn(new Attempt());
        $proceedingJoinPoint = m::mock(ProceedingJoinPoint::class);
        $annotation = new CircuitBreaker();
        $breaker = new \Hyperf\CircuitBreaker\CircuitBreaker($container, 'TestClass::testMethod');
        $factory = m::mock(CircuitBreakerFactory::class);
        $logger = m::mock(LoggerInterface::class);
        $logger->allows('debug')->once();
        $container->allows('get')->once()->with(CircuitBreakerFactory::class)->andReturn($factory);
        $container->allows('has')->once()->with(LoggerInterface::class)->andReturn(true);
        $container->allows('get')->once()->with(LoggerInterface::class)->andReturn($logger);
        $factory->allows('get')->with('TestClass::testMethod')->andReturn($breaker);
        $proceedingJoinPoint->className = 'TestClass';
        $proceedingJoinPoint->methodName = 'testMethod';

        $handler = new HandleStub($container);
        $result = $handler->handle($proceedingJoinPoint, $annotation);

        $this->assertEquals('processed', $result);
    }

    public function testHandleFallbacksWhenAttemptFails()
    {
        $container = m::mock(ContainerInterface::class);
        $container->allows('get')->once()->with(Attempt::class)->andReturn(new Attempt());
        $proceedingJoinPoint = m::mock(ProceedingJoinPoint::class);
        $annotation = new CircuitBreaker();
        $breaker = new \Hyperf\CircuitBreaker\CircuitBreaker($container, 'TestClass::testMethod');
        $factory = m::mock(CircuitBreakerFactory::class);
        $logger = m::mock(LoggerInterface::class);
        $logger->allows('debug')->once();
        $container->allows('get')->once()->with(CircuitBreakerFactory::class)->andReturn($factory);
        $container->allows('has')->once()->with(LoggerInterface::class)->andReturn(true);
        $container->allows('get')->once()->with(LoggerInterface::class)->andReturn($logger);
        $factory->allows('get')->with('TestClass::testMethod')->andReturn($breaker);
        $breaker->halfOpen();
        $breaker->incrFailCounter();
        $proceedingJoinPoint->className = 'TestClass';
        $proceedingJoinPoint->methodName = 'testMethod';

        $handler = new HandleStub($container);
        try {
            $handler->handle($proceedingJoinPoint, $annotation);
        } catch (Exception $e) {
            $this->assertSame(BadFallbackException::class, get_class($e));
        }
    }
}
