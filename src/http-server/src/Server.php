<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\OnRequestInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\Framework\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Throwable;

class Server implements OnRequestInterface, MiddlewareInitializerInterface
{
    /**
     * @var array
     */
    private $middlewares;

    /**
     * @var string
     */
    private $coreHandler;

    /**
     * @var MiddlewareInterface
     */
    private $coreMiddleware;

    /**
     * @var array
     */
    private $exceptionHandlers;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var HttpDispatcher
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $serverName;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(
        string $serverName = 'http',
        string $coreHandler,
        ContainerInterface $container
    ) {
        $this->serverName = $serverName;
        $this->coreHandler = $coreHandler;
        $this->container = $container;
        $this->dispatcher = $container->get(HttpDispatcher::class);
        $this->logger = $this->container->get(StdoutLoggerInterface::class);
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $coreHandler = $this->coreHandler;
        $this->coreMiddleware = new $coreHandler($this->container, $serverName);

        $config = $this->container->get(ConfigInterface::class);
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, [
            HttpExceptionHandler::class,
        ]);
    }

    public function onRequest(SwooleRequest $request, SwooleResponse $response): void
    {
        try {
            // Initialize PSR-7 Request and Response objects.
            Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
            Context::set(ResponseInterface::class, $psr7Response = new Psr7Response($response));

            $middlewares = array_merge($this->middlewares, MiddlewareManager::get($this->serverName, $psr7Request->getUri()->getPath(), $psr7Request->getMethod()));

            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);
        } catch (Throwable $throwable) {
            if (! $throwable instanceof ServerException) {
                $message = sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile());
                $this->logger->error($message);
            }
            // Delegate the exception to exception handler.
            $exceptionHandlerDispatcher = $this->container->get(ExceptionHandlerDispatcher::class);
            $psr7Response = $exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        } finally {
            // Send the Response to client.
            $psr7Response->send();
        }
    }
}
