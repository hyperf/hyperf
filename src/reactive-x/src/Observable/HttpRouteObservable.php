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
namespace Hyperf\ReactiveX\Observable;

use Hyperf\Dispatcher\HttpRequestHandler;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Server;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class HttpRouteObservable extends Observable
{
    /**
     * @var string|string[]
     */
    private $httpMethod;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var null|callable|string
     */
    private $callback;

    /**
     * @var null|SchedulerInterface
     */
    private $scheduler;

    public function __construct($httpMethod, string $uri, $callback = null, SchedulerInterface $scheduler = null)
    {
        $this->scheduler = $scheduler;
        $this->httpMethod = $httpMethod;
        $this->uri = $uri;
        $this->callback = $callback;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $container = ApplicationContext::getContainer();
        $factory = $container->get(DispatcherFactory::class);
        $factory->getRouter('http')->addRoute($this->httpMethod, $this->uri, function () use ($observer, $container) {
            $request = Context::get(ServerRequestInterface::class);
            if ($this->scheduler === null) {
                $this->scheduler = Scheduler::getDefault();
            }
            $this->scheduler->schedule(function () use ($observer, $request) {
                $observer->onNext($request);
            });
            if ($this->callback !== null) {
                $serverName = $container->get(Server::class)->getServerName();
                $middleware = new CoreMiddleware($container, $serverName);
                $handler = new HttpRequestHandler([], new \stdClass(), $container);
                /** @var Dispatched $dispatched */
                $dispatched = $request->getAttribute(Dispatched::class);
                $dispatched->handler->callback = $this->callback;
                return $middleware->process($request->withAttribute(Dispatched::class, $dispatched), $handler);
            }
            return ['status' => 200];
        });
        return new EmptyDisposable();
    }
}
