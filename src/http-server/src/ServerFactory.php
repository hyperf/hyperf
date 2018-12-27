<?php

namespace Hyperflex\HttpServer;


use Hyperflex\Contracts\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperflex\HttpServer\Exception\Handler\HttpExceptionHandler;

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