<?php

namespace Hyperflex\GrpcServer;

use Hyperflex\HttpServer\ServerFactory as HttpServerFactory;

class ServerFactory extends HttpServerFactory
{
    protected $coreMiddleware = CoreMiddleware::class;
}
