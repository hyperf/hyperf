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

namespace HyperfTest\Event;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Annotation\Listener as ListenerAnnotation;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerData;
use Hyperf\Event\ListenerProvider;
use Hyperf\Event\ListenerProviderFactory;
use Hyperf\Stdlib\SplPriorityQueue;
use HyperfTest\Event\Event\Alpha;
use HyperfTest\Event\Event\Beta;
use HyperfTest\Event\Listener\AlphaListener;
use HyperfTest\Event\Listener\BetaListener;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversClass(ListenerAnnotation::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(ListenerProvider::class)]
#[CoversClass(ListenerProviderFactory::class)]
class ListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInvokeListenerProvider()
    {
        $listenerProvider = new ListenerProvider();
        $this->assertInstanceOf(ListenerProviderInterface::class, $listenerProvider);
        $this->assertTrue(is_array($listenerProvider->listeners));
    }

    public function testInvokeListenerProviderWithListeners()
    {
        $listenerProvider = new ListenerProvider();
        $this->assertInstanceOf(ListenerProviderInterface::class, $listenerProvider);

        $listenerProvider->on(Alpha::class, [new AlphaListener(), 'process']);
        $listenerProvider->on(Beta::class, [new BetaListener(), 'process']);
        $this->assertTrue(is_array($listenerProvider->listeners));
        $this->assertSame(2, count($listenerProvider->listeners));
        $this->assertInstanceOf(SplPriorityQueue::class, $listenerProvider->getListenersForEvent(new Alpha()));
    }

    public function testListenerProcess()
    {
        $listenerProvider = new ListenerProvider();
        $listenerProvider->on(Alpha::class, [$listener = new AlphaListener(), 'process']);
        $this->assertSame(1, $listener->value);

        $dispatcher = new EventDispatcher($listenerProvider);
        $dispatcher->dispatch(new Alpha());
        $this->assertSame(2, $listener->value);
    }

    public function testListenerInvokeByFactory()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([]));
        $container->shouldReceive('get')
            ->once()
            ->with(ListenerProviderInterface::class)
            ->andReturn((new ListenerProviderFactory())($container));
        $listenerProvider = $container->get(ListenerProviderInterface::class);
        $this->assertInstanceOf(ListenerProviderInterface::class, $listenerProvider);
    }

    public function testListenerInvokeByFactoryWithConfig()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'listeners' => [
                AlphaListener::class,
                BetaListener::class,
            ],
        ]));
        $container->shouldReceive('get')
            ->with(AlphaListener::class)
            ->andReturn($alphaListener = new AlphaListener());
        $container->shouldReceive('get')
            ->with(BetaListener::class)
            ->andReturn($betaListener = new BetaListener());
        $container->shouldReceive('get')
            ->once()
            ->with(ListenerProviderInterface::class)
            ->andReturn((new ListenerProviderFactory())($container));
        $listenerProvider = $container->get(ListenerProviderInterface::class);
        $this->assertInstanceOf(ListenerProviderInterface::class, $listenerProvider);
        $this->assertSame(2, count($listenerProvider->listeners));

        $dispatcher = new EventDispatcher($listenerProvider);
        $this->assertSame(1, $alphaListener->value);
        $dispatcher->dispatch(new Alpha());
        $this->assertSame(2, $alphaListener->value);
        $this->assertSame(1, $betaListener->value);
        $dispatcher->dispatch(new Beta());
        $this->assertSame(2, $betaListener->value);
    }

    public function testListenerInvokeByFactoryWithAnnotationConfig()
    {
        $listenerAnnotation = new ListenerAnnotation();
        $listenerAnnotation->collectClass(AlphaListener::class, ListenerAnnotation::class);
        $listenerAnnotation->collectClass(BetaListener::class, ListenerAnnotation::class);

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([]));
        $container->shouldReceive('get')
            ->with(AlphaListener::class)
            ->andReturn($alphaListener = new AlphaListener());
        $container->shouldReceive('get')
            ->with(BetaListener::class)
            ->andReturn($betaListener = new BetaListener());
        $container->shouldReceive('get')
            ->once()
            ->with(ListenerProviderInterface::class)
            ->andReturn((new ListenerProviderFactory())($container));

        $listenerProvider = $container->get(ListenerProviderInterface::class);
        $this->assertInstanceOf(ListenerProviderInterface::class, $listenerProvider);
        $this->assertSame(2, count($listenerProvider->listeners));

        $dispatcher = new EventDispatcher($listenerProvider);
        $this->assertSame(1, $alphaListener->value);
        $dispatcher->dispatch(new Alpha());
        $this->assertSame(2, $alphaListener->value);
        $this->assertSame(1, $betaListener->value);
        $dispatcher->dispatch(new Beta());
        $this->assertSame(2, $betaListener->value);
    }

    public function testListenerAnnotationWithPriority()
    {
        $listenerAnnotation = new ListenerAnnotation();
        $this->assertSame(ListenerData::DEFAULT_PRIORITY, $listenerAnnotation->priority);

        $listenerAnnotation = new ListenerAnnotation(2);
        $this->assertSame(2, $listenerAnnotation->priority);
    }
}
