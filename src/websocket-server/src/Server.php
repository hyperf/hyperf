<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\Utils\Context;
use Hyperf\WebSocketServer\Collector\FdCollector;
use Hyperf\WebSocketServer\Exception\Handler\WebSocketExceptionHandler;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
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
     * @var CoreMiddleware
     */
    protected $coreMiddleware;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var WebSocketServer
     */
    protected $server;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var string
     */
    protected $serverName = 'websocket';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dispatcher = $container->get(HttpDispatcher::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
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

    public function setServer(WebSocketServer $server): void
    {
        $this->server = $server;
    }

    public function onHandShake(SwooleRequest $request, SwooleResponse $response): void
    {
        try {
            $security = $this->container->get(Security::class);
            $fd = $request->fd;

            $psr7Request = $this->initRequest($request);
            $psr7Response = $this->initResponse($response);

            $this->logger->debug("WebSocket: fd[{$fd}] start a handshake request.");

            $key = $psr7Request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
            if ($security->isInvalidSecurityKey($key)) {
                throw new WebSocketHandeShakeException('sec-websocket-key is invalid!');
            }

            $middlewares = array_merge($this->middlewares, MiddlewareManager::get($this->serverName, $psr7Request->getUri()->getPath(), $psr7Request->getMethod()));

            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);

            $class = $psr7Response->getAttribute('class');

            FdCollector::set($fd, $class);

            defer(function () use ($request, $class) {
                $instance = $this->container->get($class);
                if ($instance instanceof OnOpenInterface) {
                    $instance->onOpen($this->server, $request);
                }
            });
        } catch (\Throwable $exception) {
            $this->logger->warning($this->container->get(FormatterInterface::class)->format($exception));
            $stream = new SwooleStream((string) $exception->getMessage());
            $psr7Response = $psr7Response->withBody($stream);
        }

        $psr7Response->send();
    }

    public function onMessage(\Swoole\Server $server, Frame $frame): void
    {
        $fdObj = FdCollector::get($frame->fd);
        if (! $fdObj) {
            $this->logger->warning(sprintf('WebSocket: fd[%d] does not exist.', $frame->fd));
            return;
        }

        $instance = $this->container->get($fdObj->class);

        if (! $instance instanceof OnMessageInterface) {
            $this->logger->warning("{$instance} is not instanceof " . OnMessageInterface::class);
            return;
        }

        $instance->onMessage($server, $frame);
    }

    public function onClose(\Swoole\Server $server, int $fd, int $reactorId): void
    {
        $this->logger->debug(sprintf('WebSocket: fd[%d] closed.', $fd));

        $fdObj = FdCollector::get($fd);
        if (! $fdObj) {
            return;
        }
        $instance = $this->container->get($fdObj->class);
        if ($instance instanceof OnCloseInterface) {
            $instance->onClose($server, $fd, $reactorId);
        }

        FdCollector::del($fd);
    }

    /**
     * Initialize PSR-7 Request.
     */
    protected function initRequest(SwooleRequest $request): RequestInterface
    {
        // Initialize PSR-7 Request and Response objects.
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        return $psr7Request;
    }

    /**
     * Initialize PSR-7 Response.
     */
    protected function initResponse(SwooleResponse $response): ResponseInterface
    {
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response($response));
        return $psr7Response;
    }
}
