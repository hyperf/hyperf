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

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\RequestContext;
use Hyperf\Dispatcher\HttpRequestHandler;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Server;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;
use stdClass;

class HttpRouteObservable extends Observable
{
    /**
     * @param string|string[] $httpMethod
     * @param null|callable|string $callback
     */
    public function __construct(
        private array|string $httpMethod,
        private string $uri,
        private mixed $callback = null,
        private ?SchedulerInterface $scheduler = null,
        private string $serverName = 'http'
    ) {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $container = ApplicationContext::getContainer();
        $factory = $container->get(DispatcherFactory::class);
        $factory->getRouter($this->serverName)->addRoute($this->httpMethod, $this->uri, function () use ($observer, $container) {
            $request = RequestContext::get();
            if ($this->scheduler === null) {
                $this->scheduler = Scheduler::getDefault();
            }
            $this->scheduler->schedule(function () use ($observer, $request) {
                $observer->onNext($request);
            });
            if ($this->callback !== null) {
                $serverName = $container->get(Server::class)->getServerName();
                $middleware = new CoreMiddleware($container, $serverName);
                $handler = new HttpRequestHandler([], new stdClass(), $container);
                /** @var Dispatched $dispatched */
                $dispatched = $request->getAttribute(Dispatched::class);
                $dispatched->handler->callback = $this->callback;
                return $middleware->process($request->setAttribute(Dispatched::class, $dispatched), $handler);
            }
            return ['status' => 200];
        });
        return new EmptyDisposable();
    }
}
