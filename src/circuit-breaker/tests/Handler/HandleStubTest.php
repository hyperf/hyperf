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

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\CircuitBreaker\Attempt;
use Hyperf\CircuitBreaker\CircuitBreakerFactory;
use Hyperf\CircuitBreaker\Exception\BadFallbackException;
use Hyperf\CircuitBreaker\LoggerInterface;
use Hyperf\CircuitBreaker\State;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use HyperfTest\CircuitBreaker\Stub\HandleStub;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class HandleStubTest extends TestCase
{
    protected function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(ApplicationContext::class);
        $reflection->setStaticPropertyValue('container', null);
        m::close();
    }

    public function testHandleReturnsProcessedResult()
    {
        /**
         * @var ContainerInterface&m\MockInterface $container
         */
        $container = ApplicationContext::getContainer();
        $container->allows('make')->with(State::class, [])->andReturn(new State());
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
        /**
         * @var ContainerInterface|m\MockInterface $container
         */
        $container = ApplicationContext::getContainer();
        $container->allows('make')->with(State::class, [])->andReturn(new State());
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
        /**
         * @var ContainerInterface|m\MockInterface $container
         */
        $container = ApplicationContext::getContainer();
        $container->allows('make')->with(State::class, [])->andReturn(new State());
        $attempt = m::mock(Attempt::class);
        $attempt->allows('attempt')->once()->andReturn(true);
        $container->allows('get')->once()->with(Attempt::class)->andReturn($attempt);
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
        $breaker->halfOpen();

        $handler = new HandleStub($container);
        $result = $handler->handle($proceedingJoinPoint, $annotation);

        $this->assertEquals('processed', $result);
    }

    public function testHandleFallbacksWhenAttemptFails()
    {
        /**
         * @var ContainerInterface|m\MockInterface $container
         */
        $container = ApplicationContext::getContainer();
        $container->allows('make')->with(State::class, [])->andReturn(new State());
        $attempt = m::mock(Attempt::class);
        $attempt->allows('attempt')->once()->andReturn(false);
        $container->allows('get')->once()->with(Attempt::class)->andReturn($attempt);
        $proceedingJoinPoint = m::mock(ProceedingJoinPoint::class);
        $annotation = new CircuitBreaker();
        $breaker = new \Hyperf\CircuitBreaker\CircuitBreaker($container, 'TestClass::testMethod');
        $factory = m::mock(CircuitBreakerFactory::class);
        $logger = m::mock(LoggerInterface::class);
        $container->allows('get')->once()->with(CircuitBreakerFactory::class)->andReturn($factory);
        $container->allows('has')->once()->with(LoggerInterface::class)->andReturn(true);
        $container->allows('get')->once()->with(LoggerInterface::class)->andReturn($logger);
        $factory->allows('get')->with('TestClass::testMethod')->andReturn($breaker);
        $breaker->halfOpen();
        $breaker->incrFailCounter();
        $proceedingJoinPoint->className = 'TestClass';
        $proceedingJoinPoint->methodName = 'testMethod';
        $handler = new HandleStub($container);
        $this->expectException(BadFallbackException::class);
        $handler->handle($proceedingJoinPoint, $annotation);
    }
}
