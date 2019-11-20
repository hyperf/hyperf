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

namespace Hyperf\ReactiveX;

use Hyperf\Dispatcher\HttpRequestHandler;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Server;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable as RxObservable;
use Rx\ObserverInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;

class Observable
{
    public static function __callStatic($method, $arguments)
    {
        return call_user_func([RxObservable::class, $method], ...$arguments);
    }

    public static function fromEvent(string $eventName): RxObservable
    {
        return RxObservable::create(function (ObserverInterface $observer) use ($eventName) {
            $provider = ApplicationContext::getContainer()->get(ListenerProviderInterface::class);
            $provider->on($eventName, function ($event) use ($observer) {
                $observer->onNext($event);
            });
            return new EmptyDisposable();
        });
    }

    public static function fromChannel(Channel $channel): RxObservable
    {
        return RxObservable::create(function (ObserverInterface $observer) use ($channel) {
            Coroutine::Create(function () use ($channel, $observer) {
                $result = $channel->pop();
                while ($result !== false) {
                    $observer->onNext($result);
                    $result = $channel->pop();
                }
                $observer->onCompleted();
            });
            return new EmptyDisposable();
        });
    }

    public static function fromHttpRoute($httpMethod, string $uri, $callback = null): RxObservable
    {
        return RxObservable::create(function (ObserverInterface $observer) use ($httpMethod, $uri, $callback) {
            $container = ApplicationContext::getContainer();
            $factory = $container->get(DispatcherFactory::class);
            $factory->getRouter('http')->addRoute($httpMethod, $uri, function () use ($observer, $callback, $container) {
                $request = Context::get(ServerRequestInterface::class);
                $observer->onNext($request);
                if ($callback !== null) {
                    $serverName = $container->get(Server::class)->getServerName();
                    $middleware = new CoreMiddleware($container, $serverName);
                    $handler = new HttpRequestHandler([], new \stdClass(), $container);
                    /** @var Dispatched $dispatched */
                    $dispatched = $request->getAttribute(Dispatched::class);
                    $dispatched->handler->callback = $callback;
                    return $middleware->process($request->withAttribute(Dispatched::class, $dispatched), $handler);
                }
                return ['status' => 200];
            });
            return new EmptyDisposable();
        });
    }

    public static function fromCoroutine(callable ...$callables): RxObservable
    {
        return RxObservable::create(function (ObserverInterface $observer) use ($callables) {
            coroutine::create(function () use ($observer, $callables) {
                $wg = new WaitGroup();
                $wg->add(count($callables));
                foreach ($callables as $callable) {
                    Coroutine::create(function () use ($observer, $callable, &$wg) {
                        try {
                            $result = $callable();
                            $observer->onNext($result);
                        } catch (\Throwable $throwable) {
                            $observer->onError($throwable);
                        } finally {
                            $wg->done();
                        }
                    });
                }

                $wg->wait();
                $observer->onCompleted();
            });

            return new EmptyDisposable();
        });
    }

    public static function unwrap(RxObservable $observable): array
    {
        $chan = new Channel(1);
        $observable->subscribe(
            function ($x) use ($chan) {
                $send = new \stdClass();
                $send->data = $x;
                $chan->push($send);
            },
            function ($x) {
                throw $x;
            },
            function () use ($chan) {
                $chan->close();
            },
        );
        $results = [];
        $receive = $chan->pop();
        while ($receive !== false) {
            $results[] = $receive->data;
            $receive = $chan->pop();
        }
        return $results;
    }

    public static function unwrapOne(RxObservable $observable)
    {
        $chan = new Channel(1);
        $id = $observable->subscribe(
            function ($x) use ($chan) {
                $send = new \stdClass();
                $send->data = $x;
                $chan->push($send, 1);
            },
            function ($x) {
                throw $x;
            },
            function () use ($chan) {
                $chan->close();
            },
        );
        $receive = $chan->pop();
        $id->dispose();
        if ($receive !== false) {
            return $receive->data;
        }
        throw new \Exception('Found no element from observable');
    }
}
