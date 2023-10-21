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
namespace Hyperf\GrpcServer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\GrpcServer\Exception\Handler\GrpcExceptionHandler;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\Event\RequestHandled;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Server as HttpServer;
use Hyperf\Support\SafeCaller;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Server extends HttpServer
{
    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $this->coreMiddleware = new CoreMiddleware($this->container, $serverName);

        $config = $this->container->get(ConfigInterface::class);
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, [
            GrpcExceptionHandler::class,
        ]);
    }

    public function onRequest($request, $response): void
    {
        try {
            CoordinatorManager::until(Constants::WORKER_START)->yield();

            [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);

            $this->option?->isEnableRequestLifecycle() && $this->event?->dispatch(new RequestReceived(
                request: $psr7Request,
                response: $psr7Response,
                server: $this->serverName
            ));

            $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);
            $middlewares = $this->middlewares;

            $registeredMiddlewares = [];
            if ($dispatched->isFound()) {
                $registeredMiddlewares = MiddlewareManager::get($this->serverName, $dispatched->handler->route, $psr7Request->getMethod());
                $middlewares = array_merge($middlewares, $registeredMiddlewares);
            }

            if ($this->option?->isMustSortMiddlewares() || $registeredMiddlewares) {
                $middlewares = MiddlewareManager::sortMiddlewares($middlewares);
            }

            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);
        } catch (Throwable $throwable) {
            // Delegate the exception to exception handler.
            $psr7Response = $this->container->get(SafeCaller::class)->call(function () use ($throwable) {
                return $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
            }, static function () {
                return (new Psr7Response())->withStatus(400);
            });
        } finally {
            if (isset($psr7Request) && $this->option?->isEnableRequestLifecycle()) {
                defer(fn () => $this->event?->dispatch(new RequestTerminated(
                    request: $psr7Request,
                    response: $psr7Response ?? null,
                    exception: $throwable ?? null,
                    server: $this->serverName
                )));

                $this->event?->dispatch(new RequestHandled(
                    request: $psr7Request,
                    response: $psr7Response ?? null,
                    exception: $throwable ?? null,
                    server: $this->serverName
                ));
            }

            // Send the Response to client.
            if (! isset($psr7Response) || ! $psr7Response instanceof ResponseInterface) {
                return;
            }
            // If the response is not writable, then ignore it.
            if (method_exists($response, 'isWritable') && ! $response->isWritable()) {
                return;
            }
            if (isset($psr7Request) && $psr7Request->getMethod() === 'HEAD') {
                $this->responseEmitter->emit($psr7Response, $response, false);
            } else {
                $this->responseEmitter->emit($psr7Response, $response);
            }
        }
    }
}
