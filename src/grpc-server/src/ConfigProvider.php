<?php

namespace Hyperf\GrpcServer;

use Hyperf\GrpcServer\Router\Dispatcher;
use Hyperf\GrpcServer\Router\DispatcherFactory;

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
