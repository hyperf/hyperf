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
use Swoole\Http\Request;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Websocket\Frame;

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
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var string
     */
    protected $serverName = 'websocket';

    public function __construct(ContainerInterface $container, string $serverName)
    {
        $this->container = $container;
        $this->serverName = $serverName;
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
            $method = $psr7Response->getAttribute('method');

            FdCollector::set($fd, $class, $method);

            $psr7Response->send();
        } catch (\Throwable $exception) {
            $this->logger->warning($this->container->get(FormatterInterface::class)->format($exception));
            $psr7Response->withBody(new SwooleStream((string) $exception->getMessage()))->send();
        }
    }

    public function onMessage(\Swoole\Server $server, Frame $frame): void
    {
        $fdObj = FdCollector::get($frame->fd);

        $class = $this->container->get($fdObj->class);
        $method = $fdObj->method;

        $class->{$method}($server, $frame);
    }

    public function onClose(\Swoole\Server $server, int $fd, int $reactorId): void
    {
        $this->logger->debug("WebSocket: fd[{$fd}] close a active connection.");

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
