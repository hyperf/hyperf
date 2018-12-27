<?php

namespace Hyperflex\HttpServer;


use Hyperflex\HttpServer\Command\StartServer;
use Hyperflex\HttpServer\Command\StartServerFactory;
use FastRoute\Dispatcher;
use Hyperflex\HttpServer\Router\DispatcherFactory;
use FastRoute\RouteCollector;
use Hyperflex\HttpServer\Router\RouteCollectorFactory;

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
                'paths' => [
                    "vendor/hyperflex/http-server/src"
                ],
            ],
        ];
    }
}