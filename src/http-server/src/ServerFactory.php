<?php

namespace Hyperf\HttpServer;


use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;

class ServerFactory
{
    protected $coreMiddleware = CoreMiddleware::class;

    public function __invoke(ContainerInterface $container): Server
    {
        $config = $container->get(ConfigInterface::class);
        $middlewares = $config->get('middlewares.http', []);
        $exceptionHandlers = $config->get('exceptions.handler.http', [
            HttpExceptionHandler::class,
        ]);
        return new Server($middlewares, $this->coreMiddleware, $exceptionHandlers, $container);
    }
}