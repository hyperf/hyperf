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
namespace HyperfTest\Cases;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Engine\Channel;
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
use Hyperf\Serializer\SimpleNormalizer;
use HyperfTest\ReactiveX\Stub\TestEvent;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable as RxObservable;
use Rx\Scheduler\EventLoopScheduler;
use Rx\SchedulerInterface;
use Swoole\Event;
use Swoole\Runtime;
use Swoole\Timer;

use function Hyperf\Coroutine\go;
use function Hyperf\Support\swoole_hook_flags;

/**
 * @internal
 * @coversNothing
 */
class ObservableTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Runtime::enableCoroutine(swoole_hook_flags());
    }

    protected function setUp(): void
    {
        $container = new Container(new DefinitionSource([]));
        $container->define(SchedulerInterface::class, EventLoopScheduler::class);
        ApplicationContext::setContainer($container);
        RxSwoole::init();
    }

    protected function tearDown(): void
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
        }, new EventLoopScheduler(function ($ms, $callable) {
            if ($ms === 0) {
                Event::defer(function () use ($callable) {
                    Runtime::enableCoroutine(SWOOLE_HOOK_FLAGS);
                    Coroutine::create($callable);
                });
                return new EmptyDisposable();
            }
            $timer = Timer::after($ms, function () use ($callable) {
                $callable();
            });
            return new CallbackDisposable(function () use ($timer) {
                Timer::clear($timer);
            });
        }));
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

    public function testInterval()
    {
        $result = new Channel(2);
        $o = Observable::interval(1);
        $o->delay(2)->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $o->skip(1)->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $this->assertEquals(1, $result->pop());
        $this->assertEquals(0, $result->pop());

        $o = Observable::interval(1);
        $o->take(1)->subscribe(
            function ($x) use ($result) {
                usleep(2000);
                $result->push($x);
            }
        );
        $o->skip(1)->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $this->assertEquals(0, $result->pop());
        $this->assertEquals(1, $result->pop());

        $o = Observable::interval(1);
        $o->take(1)->subscribe(
            function ($x) use ($result) {
                go(function () use ($result, $x) {
                    usleep(2000);
                    $result->push($x);
                });
            }
        );
        $o->skip(1)->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $this->assertEquals(1, $result->pop());
        $this->assertEquals(0, $result->pop());
    }

    public function testCoroutine()
    {
        $result = new Channel(1);
        $o = Observable::fromCoroutine([function () {
            usleep(2000);
            return 24;
        }, function () {
            usleep(1000);
            return 42;
        }]);
        $o->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $this->assertEquals(42, $result->pop());
        $o = Observable::fromCoroutine([function () {
            usleep(1000);
            return 24;
        }, function () {
            usleep(2000);
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
