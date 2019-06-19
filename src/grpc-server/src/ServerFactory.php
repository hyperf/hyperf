<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GrpcServer;

use Hyperf\HttpServer\ServerFactory as HttpServerFactory;

class ServerFactory extends HttpServerFactory
{
    protected $coreMiddleware = CoreMiddleware::class;
}
