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

namespace Hyperf\GrpcServer;

use Hyperf\GrpcServer\Router\Dispatcher;
use Hyperf\GrpcServer\Router\DispatcherFactory;
use Hyperf\GrpcServer\Router\RouteCollector;
use Hyperf\HttpServer\Router\RouteCollectorFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Server::class => ServerFactory::class,
                Dispatcher::class => DispatcherFactory::class,
                RouteCollector::class => RouteCollectorFactory::class,
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}
