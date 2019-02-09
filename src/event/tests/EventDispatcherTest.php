<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Event;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\EventDispatcherFactory;
use Hyperf\Event\ListenerProvider;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Logger\StdoutLogger;
use HyperfTest\Event\Event\Alpha;
use HyperfTest\Event\Listener\AlphaListener;
use HyperfTest\Event\Listener\BetaListener;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionClass;

/**
 * @internal
 * @covers \Hyperf\Event\EventDispatcher
 */
class EventDispatcherTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInvokeDispatcher()
    {
        $listeners = Mockery::mock(ListenerProviderInterface::class);
        $this->assertInstanceOf(EventDispatcherInterface::class, new EventDispatcher($listeners));
    }

    public function testInvokeDispatcherWithStdoutLogger()
    {
        $listeners = Mockery::mock(ListenerProviderInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $this->assertInstanceOf(EventDispatcherInterface::class, $instance = new EventDispatcher($listeners, $logger));
        $reflectionClass = new ReflectionClass($instance);
        $loggerProperty = $reflectionClass->getProperty('logger');
        $loggerProperty->setAccessible(true);
        $this->assertInstanceOf(StdoutLoggerInterface::class, $loggerProperty->getValue($instance));
    }

    public function testInvokeDispatcherByFactory()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([]));
        $config = $container->get(ConfigInterface::class);
        $container->shouldReceive('get')->with(ListenerProviderInterface::class)->andReturn(new ListenerProvider());
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(new StdoutLogger($config));
        $this->assertInstanceOf(EventDispatcherInterface::class, $instance = (new EventDispatcherFactory())($container));
        $reflectionClass = new ReflectionClass($instance);
        $loggerProperty = $reflectionClass->getProperty('logger');
        $loggerProperty->setAccessible(true);
        $this->assertInstanceOf(StdoutLoggerInterface::class, $loggerProperty->getValue($instance));
    }

    public function testStoppable()
    {
        $listeners = new ListenerProvider();
        $listeners->on(Alpha::class, [$alphaListener = new AlphaListener(), 'process']);
        $listeners->on(Alpha::class, [$betaListener = new BetaListener(), 'process']);
        $dispatcher = new EventDispatcher($listeners);
        $dispatcher->dispatch((new Alpha())->setPropagation(true));
        $this->assertSame(2, $alphaListener->value);
        $this->assertSame(1, $betaListener->value);
    }

    public function testLoggerDump()
    {
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive('debug');
        $listenerProvider = new ListenerProvider();
        $listenerProvider->on(Alpha::class, [new AlphaListener(), 'process']);
        $dispatcher = new EventDispatcher($listenerProvider, $logger);
        $dispatcher->dispatch(new Alpha());
    }
}
