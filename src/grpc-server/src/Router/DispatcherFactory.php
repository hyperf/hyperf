<?php

namespace Hyperf\GrpcServer\Router;

use Hyperf\HttpServer\Router\DispatcherFactory as HttpDispatcherFactory;

class DispatcherFactory extends HttpDispatcherFactory
{
    protected $routes = [BASE_PATH . '/config/grpc_routes.php'];
}
