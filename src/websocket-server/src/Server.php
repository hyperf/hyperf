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
use Hyperf\Contract\OnRequestInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;
use Hyperf\HttpServer\Server as HttpServer;
use Psr\Container\ContainerInterface;

class Server extends HttpServer implements MiddlewareInitializerInterface, OnRequestInterface
{
    public function __construct(ContainerInterface $container, string $serverName)
    {
        $this->container = $container;
        $this->serverName = $serverName;
        $this->dispatcher = $container->get(HttpDispatcher::class);
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $this->coreMiddleware = new CoreMiddleware($this->container, $serverName);

        $config = $this->container->get(ConfigInterface::class);
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, [
            HttpExceptionHandler::class,
        ]);
    }
}
