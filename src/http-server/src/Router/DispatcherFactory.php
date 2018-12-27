<?php

namespace Hyperf\HttpServer\Router;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\Dispatcher;
use Psr\Container\ContainerInterface;
use FastRoute\RouteParser\Std;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;

class DispatcherFactory
{
    protected $routes = [BASE_PATH . '/config/routes.php'];

    public function __invoke(ContainerInterface $container): Dispatcher
    {
        $parser = new Std();
        $generator = new DataGenerator();
        /** @var RouteCollector $routeCollector */
        $router = new RouteCollector($parser, $generator);
        Router::init($router);

        foreach ($this->routes as $route) {
            require_once $route;
        }

        return new GroupCountBased($router->getData());
    }
}