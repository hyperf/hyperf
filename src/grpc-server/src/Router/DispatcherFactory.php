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

namespace Hyperf\GrpcServer\Router;

use Hyperf\HttpServer\Router\DispatcherFactory as HttpDispatcherFactory;

class DispatcherFactory extends HttpDispatcherFactory
{
    protected $routes = [BASE_PATH . '/config/grpc_routes.php'];
}
