<?php

namespace Hyperf\GrpcServer;

use Hyperf\HttpServer\ServerFactory as HttpServerFactory;

class ServerFactory extends HttpServerFactory
{
    protected $coreMiddleware = CoreMiddleware::class;
}
