<?php

namespace HyperfTest\Event;


use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use HyperfTest\Event\Event\Alpha;
use HyperfTest\Event\Event\Beta;
use HyperfTest\Event\Listener\AlphaListener;
use HyperfTest\Event\Listener\BetaListener;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcherTest extends TestCase
{
    public function testInvokeDispatcher()
    {
        $listeners = Mockery::mock(ListenerProviderInterface::class);
        $this->assertInstanceOf(EventDispatcherInterface::class, new EventDispatcher($listeners));
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


}