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

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Psr\Container\ContainerInterface;

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
