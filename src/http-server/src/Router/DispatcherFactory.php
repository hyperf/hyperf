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

namespace Hyperf\HttpServer\Router;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use Psr\Container\ContainerInterface;

class DispatcherFactory
{
    protected $routes = [BASE_PATH . '/config/routes.php'];

    protected $routeCollector = RouteCollector::class;

    public function __invoke(ContainerInterface $container): Dispatcher
    {
        /** @var RouteCollector $router */
        $router = $container->get($this->routeCollector);

        foreach ($this->routes as $route) {
            require_once $route;
        }

        // Add routes from RouteMetadataCollector.
        $metadata = RouteMetadataCollector::getContainer();
        foreach ($metadata ?? [] as $path => $item) {
            $router->addRoute($item['method'], $path, $item['handler']);
        }

        return new GroupCountBased($router->getData());
    }
}
