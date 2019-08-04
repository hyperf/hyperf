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

namespace Hyperf\RpcServer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\DispatcherInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\OnReceiveInterface;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;
use Hyperf\Rpc\Protocol;
use Hyperf\Server\ServerManager;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server as SwooleServer;
use Throwable;

abstract class Server implements OnReceiveInterface, MiddlewareInitializerInterface
{
    /**
     * @var array
     */
    protected $middlewares;

    /**
     * @var string
     */
    protected $coreHandler;

    /**
     * @var MiddlewareInterface
     */
    protected $coreMiddleware;

    /**
     * @var array
     */
    protected $exceptionHandlers;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $serverName;

    /**
     * @var Protocol
     */
    protected $protocol;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        string $serverName,
        string $coreHandler,
        ContainerInterface $container,
        Protocol $protocol,
        $dispatcher,
        LoggerInterface $logger
    ) {
        $this->serverName = $serverName;
        $this->coreHandler = $coreHandler;
        $this->container = $container;
        $this->protocol = $protocol;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $this->coreMiddleware = $this->createCoreMiddleware();

        $config = $this->container->get(ConfigInterface::class);
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, [
            HttpExceptionHandler::class,
        ]);
    }

    public function onReceive(SwooleServer $server, int $fd, int $fromId, string $data): void
    {
        $request = $response = null;
        try {
            // Initialize PSR-7 Request and Response objects.
            Context::set(ServerRequestInterface::class, $request = $this->buildRequest($fd, $fromId, $data));
            Context::set(ResponseInterface::class, $this->buildResponse($fd, $server));

            // $middlewares = array_merge($this->middlewares, MiddlewareManager::get());
            $middlewares = $this->middlewares;

            $response = $this->dispatcher->dispatch($request, $middlewares, $this->coreMiddleware);
        } catch (Throwable $throwable) {
            // Delegate the exception to exception handler.
            $exceptionHandlerDispatcher = $this->container->get(ExceptionHandlerDispatcher::class);
            $response = $exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        } finally {
            if (! $response || ! $response instanceof ResponseInterface) {
                $response = $this->transferToResponse($response);
            }
            if ($response) {
                $server->send($fd, (string) $response->getBody());
            }
        }
    }

    public function onConnect(SwooleServer $server)
    {
        // $server is the main server object, not the server object that this callback on.
        /* @var \Swoole\Server\Port */
        [$type, $port] = ServerManager::get($this->serverName);
        $this->logger->debug(sprintf('Connect to %s:%d', $port->host, $port->port));
    }

    protected function createCoreMiddleware(): MiddlewareInterface
    {
        $coreHandler = $this->coreHandler;
        return new $coreHandler($this->container, $this->protocol, $this->serverName);
    }

    abstract protected function buildRequest(int $fd, int $fromId, string $data): ServerRequestInterface;

    abstract protected function buildResponse(int $fd, SwooleServer $server): ResponseInterface;

    protected function transferToResponse($response): ?ResponseInterface
    {
        $psr7Response = Context::get(ResponseInterface::class);
        if ($psr7Response instanceof ResponseInterface) {
            return $psr7Response->withBody(new SwooleStream($response));
        }
        return null;
    }
}
