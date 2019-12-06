<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Cases;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Server;
use Hyperf\ReactiveX\Observable;
use Hyperf\ReactiveX\RxSwoole;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use HyperfTest\ReactiveX\Stub\TestEvent;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Rx\Observable as RxObservable;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\System;

/**
 * @internal
 * @coversNothing
 */
class ObservableTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        RxSwoole::init();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testRoute()
    {
        $result = new Channel(1);
        $factory = new DispatcherFactory();
        $container = $this->getContainer($factory);
        ApplicationContext::setContainer($container);
        $o = Observable::fromHttpRoute('GET', '/hello', function () {
            $this->assertTrue(true);
            Context::set(ResponseInterface::class, new Response());
            return 'ok';
        });
        $o->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x->url());
            }
        );
        $request = new Request('GET', new Uri('/hello'));
        $middleware = new CoreMiddleware($this->getContainer($factory), 'http');
        $request = $middleware->dispatch($request);
        $dispatched = $request->getAttribute(Dispatched::class);

        $callback = $dispatched->handler->callback;
        $callback();
        $this->assertEquals('/hello', $result->pop());
    }

    public function testUnwrap()
    {
        $this->assertEquals([42], Observable::unwrap(RxObservable::of(42)));
        $this->assertEquals([42, 24], Observable::unwrap(RxObservable::fromArray([42, 24])));
        $this->assertEquals([1, 2, 3], Observable::unwrap(RxObservable::interval(1)->skip(1)->take(3)));
        $this->assertEquals(42, Observable::unwrapOne(RxObservable::of(42)));
        $this->assertEquals(1, Observable::unwrapOne(RxObservable::interval(1)->skip(1)->take(3)));
    }

    public function testFromEvent()
    {
        $result = new Channel(1);
        $listenerProvider = new ListenerProvider();
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ListenerProviderInterface::class)
            ->andReturn($listenerProvider);
        ApplicationContext::setContainer($container);

        $o = Observable::fromEvent(TestEvent::class);
        $o->subscribe(
            function ($x) use ($result) {
                $result->push($x->value);
            }
        );

        $dispatcher = new EventDispatcher($listenerProvider);
        $event = new TestEvent();
        $event->value = 42;
        $dispatcher->dispatch($event);
        $this->assertEquals(42, $result->pop());
    }

    public function testChannel()
    {
        $result = new Channel(1);
        $ch = new Channel(1);
        $o = Observable::fromChannel($ch);
        $ch->push(42);
        $o->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $this->assertEquals(42, $result->pop());
    }

    public function testCoroutine()
    {
        $result = new Channel(1);
        $o = Observable::fromCoroutine([function () {
            System::sleep(0.002);
            return 24;
        }, function () {
            System::sleep(0.001);
            return 42;
        }]);
        $o->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $this->assertEquals(42, $result->pop());
        $o = Observable::fromCoroutine([function () {
            System::sleep(0.01);
            return 24;
        }, function () {
            System::sleep(0.01);
            return 42;
        }]);
        $o->timeout(15)->subscribe(
            function ($x) {
            },
            function ($x) {
            },
            function () use ($result) {
                $result->push(0);
            }
        );
        $this->assertEquals(0, $result->pop());
    }

    protected function getContainer($factory)
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(DispatcherFactory::class)->andReturn($factory);
        $container->shouldReceive('get')->with(Server::class)->andReturn(Mockery::mock(Server::class, function ($mock) {
            $mock->shouldReceive('getServerName')->andReturns('http');
        }));
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)
            ->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('get')->with(NormalizerInterface::class)
            ->andReturn(new SimpleNormalizer());
        $container->shouldReceive('has')
            ->andReturn(false);
        return $container;
    }
}
