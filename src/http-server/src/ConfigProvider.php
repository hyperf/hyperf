<?php

namespace Hyperf\HttpServer;


use Hyperf\HttpServer\Command\StartServer;
use Hyperf\HttpServer\Command\StartServerFactory;
use FastRoute\Dispatcher;
use Hyperf\HttpServer\Router\DispatcherFactory;
use FastRoute\RouteCollector;
use Hyperf\HttpServer\Router\RouteCollectorFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Server::class => ServerFactory::class,
                StartServer::class => StartServerFactory::class,
                Dispatcher::class => DispatcherFactory::class,
            ],
            'commands' => [
                StartServer::class,
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}