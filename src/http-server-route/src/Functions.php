<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\HttpServerRoute;

use Hyperf\Utils\ApplicationContext;

/**
 * Get the path by the route name.
 */
function route(string $name, array $variables = [], string $server = 'http'): string
{
    $container = ApplicationContext::getContainer();
    $collector = $container->get(RouteCollector::class);
    return $collector->getPath($name, $variables, $server);
}
