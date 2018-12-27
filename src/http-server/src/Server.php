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

use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\Framework\ExceptionHandlerDispatcher;
use Hyperf\HttpServer\Exception\HttpException;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoft\Http\Message\Server\Request as Psr7Request;
use Swoft\Http\Message\Server\Response as Psr7Response;
use Throwable;

class Server
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
     * @var array
     */
    private $exceptionHandlers;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

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

    public function onRequest(SwooleRequest $request, SwooleResponse $response): void
    {
        try {
            // Initialize PSR-7 Request and Response objects.
            $psr7Request = Psr7Request::loadFromSwooleRequest($request);
            $psr7Response = new Psr7Response($response);
            Context::set(ServerRequestInterface::class, $psr7Request);
            Context::set(ResponseInterface::class, $psr7Response);
            $dispatcher = $this->container->get(HttpDispatcher::class);
            $psr7Response = $dispatcher->dispatch($psr7Request, $this->middlewares, $this->coreHandler);
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
