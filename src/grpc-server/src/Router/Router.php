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

use Hyperf\HttpServer\Router\Router as HttpServerRouter;

class Router extends HttpServerRouter
{
    protected static $defautCollector = RouteCollector::class;
}
