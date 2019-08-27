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

namespace Hyperf\GrpcServer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\GrpcServer\Exception\Handler\GrpcExceptionHandler;
use Hyperf\HttpServer\Server as HttpServer;
use Psr\Container\ContainerInterface;

class Server extends HttpServer
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dispatcher = $container->get(HttpDispatcher::class);
    }

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
}
