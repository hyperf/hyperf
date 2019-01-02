<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use Hyperf\Contract\ServerOnRequestInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\Framework\ExceptionHandlerDispatcher;
use Hyperf\HttpServer\Exception\HttpException;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Swoft\Http\Message\Server\Request as Psr7Request;
use Swoft\Http\Message\Server\Response as Psr7Response;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Throwable;

class Server implements ServerOnRequestInterface
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
     * @var string
     */
    private $serverName = 'http';

    public function __construct(
        array $middlewares,
        string $coreHandler,
        array $exceptionHandlers,
        ContainerInterface $container
    ) {
        $this->middlewares = $middlewares;
        $this->coreHandler = $coreHandler;
        $this->exceptionHandlers = $exceptionHandlers;
        $this->container = $container;
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $coreHandler = $this->coreHandler;
        $this->coreMiddleware = new $coreHandler($this->container, $serverName);
    }

    public function onRequest(SwooleRequest $request, SwooleResponse $response): void
    {
        try {
            // Initialize PSR-7 Request and Response objects.
            $psr7Request = Psr7Request::loadFromSwooleRequest($request);
            $psr7Response = new Psr7Response($response);
            Context::set(ServerRequestInterface::class, $psr7Request);
            Context::set(ResponseInterface::class, $psr7Response);
            $dispatcher = $this->container->get(HttpDispatcher::class);

            $psr7Response = $dispatcher->dispatch($psr7Request, $this->middlewares, $this->coreMiddleware);
        } catch (Throwable $throwable) {
            if (! $throwable instanceof HttpException) {
                $logger = $this->container->get(StdoutLoggerInterface::class);
                $logger->error($throwable->getMessage());
            }
            // Delegate the exception to exception handler.
            $exceptionHandlerDispatcher = $this->container->get(ExceptionHandlerDispatcher::class);
            $psr7Response = $exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        } finally {
            // Response and Recycle resources.
            $psr7Response->send();
            Context::destroy();
        }
    }
}
