<?php

namespace Hyperflex\GrpcServer;

use Hyperflex\GrpcServer\Router\Dispatcher;
use Hyperflex\GrpcServer\Router\DispatcherFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Server::class => ServerFactory::class,
                Dispatcher::class => DispatcherFactory::class,
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}
