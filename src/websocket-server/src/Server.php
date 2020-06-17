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
namespace Hyperf\WebSocketServer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnHandShakeInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\Contract\CoreMiddlewareInterface;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\ResponseEmitter;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\ServerManager;
use Hyperf\Server\SwooleEvent;
use Hyperf\Utils\Context;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\WebSocketServer\Collector\FdCollector;
use Hyperf\WebSocketServer\Context as WsContext;
use Hyperf\WebSocketServer\Exception\Handler\WebSocketExceptionHandler;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\CloseFrame;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class Server implements MiddlewareInitializerInterface, OnHandShakeInterface, OnCloseInterface, OnMessageInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpDispatcher
     */
    protected $dispatcher;

    /**
     * @var ExceptionHandlerDispatcher
     */
    protected $exceptionHandlerDispatcher;

    /**
     * @var CoreMiddlewareInterface
     */
    protected $coreMiddleware;

    /**
     * @var array
     */
    protected $exceptionHandlers;

    /**
     * @var ResponseEmitter
     */
    protected $responseEmitter;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var string
     */
    protected $serverName = 'websocket';

    public function __construct(
        ContainerInterface $container,
        HttpDispatcher $dispatcher,
        ExceptionHandlerDispatcher $exceptionHandlerDispatcher,
        ResponseEmitter $responseEmitter,
        StdoutLoggerInterface $logger
    ) {
        $this->container = $container;
        $this->dispatcher = $dispatcher;
        $this->exceptionHandlerDispatcher = $exceptionHandlerDispatcher;
        $this->responseEmitter = $responseEmitter;
        $this->logger = $logger;
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $this->coreMiddleware = new CoreMiddleware($this->container, $serverName);

        $config = $this->container->get(ConfigInterface::class);
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, [
            WebSocketExceptionHandler::class,
        ]);
    }

    /**
     * @return \Swoole\Coroutine\Http\Server|WebSocketServer
     */
    public function getServer()
    {
        return $this->container->get(SwooleServer::class);
    }

    public function onHandShake(SwooleRequest $request, SwooleResponse $response): void
    {
        try {
            CoordinatorManager::until(Constants::WORKER_START)->yield();
            $fd = $request->fd;
            Context::set(WsContext::FD, $fd);
            $security = $this->container->get(Security::class);

            $psr7Request = $this->initRequest($request);
            $psr7Response = $this->initResponse($response);

            $this->logger->debug(sprintf('WebSocket: fd[%d] start a handshake request.', $fd));

            $key = $psr7Request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
            if ($security->isInvalidSecurityKey($key)) {
                throw new WebSocketHandeShakeException('sec-websocket-key is invalid!');
            }

            $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);
            $middlewares = $this->middlewares;
            if ($dispatched->isFound()) {
                $registedMiddlewares = MiddlewareManager::get($this->serverName, $dispatched->handler->route, $psr7Request->getMethod());
                $middlewares = array_merge($middlewares, $registedMiddlewares);
            }

            /** @var Response $psr7Response */
            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);

            $class = $psr7Response->getAttribute('class');

            if (! empty($class)) {
                FdCollector::set($fd, $class);
                $server = $this->getServer();
                if ($server instanceof \Swoole\Coroutine\Http\Server) {
                    $response->upgrade();
                    [,,$callbacks] = ServerManager::get($this->serverName);

                    [$onMessageCallbackClass, $onMessageCallbackMethod] = $callbacks[SwooleEvent::ON_MESSAGE];
                    $onMessageCallbackInstance = $this->container->get($onMessageCallbackClass);

                    [$onCloseCallbackClass, $onCloseCallbackMethod] = $callbacks[SwooleEvent::ON_CLOSE];
                    $onCloseCallbackInstance = $this->container->get($onCloseCallbackClass);
                    while (true) {
                        $frame = $response->recv();
                        if ($frame === false) {
                            // When close the connection by server-side, the $frame is false.
                            break;
                        }
                        if ($frame instanceof CloseFrame || $frame === '') {
                            // The connection is closed.
                            $onCloseCallbackInstance->{$onCloseCallbackMethod}($response, $fd, 0);
                            break;
                        }
                        $onMessageCallbackInstance->{$onMessageCallbackMethod}($response, $frame);
                    }
                } else {
                    defer(function () use ($request, $class, $server) {
                        $instance = $this->container->get($class);
                        if ($instance instanceof OnOpenInterface) {
                            $instance->onOpen($server, $request);
                        }
                    });
                }
            }
        } catch (\Throwable $throwable) {
            // Delegate the exception to exception handler.
            $psr7Response = $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        } finally {
            // Send the Response to client.
            if (! $psr7Response || ! $psr7Response instanceof Psr7Response) {
                return;
            }
            $this->responseEmitter->emit($psr7Response, $response, true);
        }
    }

    public function onMessage($server, Frame $frame): void
    {
        if ($server instanceof SwooleResponse) {
            $fd = $server->fd;
        } else {
            $fd = $frame->fd;
        }
        Context::set(WsContext::FD, $fd);
        $fdObj = FdCollector::get($fd);
        if (! $fdObj) {
            $this->logger->warning(sprintf('WebSocket: fd[%d] does not exist.', $fd));
            return;
        }

        $instance = $this->container->get($fdObj->class);

        if (! $instance instanceof OnMessageInterface) {
            $this->logger->warning("{$instance} is not instanceof " . OnMessageInterface::class);
            return;
        }

        $instance->onMessage($server, $frame);
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $this->logger->debug(sprintf('WebSocket: fd[%d] closed.', $fd));

        $fdObj = FdCollector::get($fd);
        if (! $fdObj) {
            return;
        }
        Context::set(WsContext::FD, $fd);
        defer(function () use ($fd) {
            // Move those functions to defer, because onClose may throw exceptions
            FdCollector::del($fd);
            WsContext::release($fd);
        });
        $instance = $this->container->get($fdObj->class);
        if ($instance instanceof OnCloseInterface) {
            $instance->onClose($server, $fd, $reactorId);
        }
    }

    /**
     * Initialize PSR-7 Request.
     */
    protected function initRequest(SwooleRequest $request): ServerRequestInterface
    {
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        WsContext::set(ServerRequestInterface::class, $psr7Request);
        return $psr7Request;
    }

    /**
     * Initialize PSR-7 Response.
     */
    protected function initResponse(SwooleResponse $response): ResponseInterface
    {
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());
        return $psr7Response;
    }
}
