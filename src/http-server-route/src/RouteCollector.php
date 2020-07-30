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

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\HttpServerRoute\Exception\RouteInvalidException;
use Hyperf\HttpServerRoute\Exception\RouteNotFoundException;
use Hyperf\Server\Server;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

class RouteCollector
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DispatcherFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $routes;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->factory = $factory = $container->get(DispatcherFactory::class);

        $config = $container->get(ConfigInterface::class);
        $servers = $config->get('server.servers', []);

        foreach ($servers as $server) {
            if (Arr::get($server, 'type') === Server::SERVER_HTTP && isset($server['name'])) {
                $serverName = $server['name'];
                [$data, $dynamic] = $factory->getRouter($serverName)->getData();
                foreach ($data as $method => $handlers) {
                    foreach ($handlers as $handler) {
                        if ($handler instanceof Handler) {
                            $name = $handler->options['name'] ?? $handler->route;
                            $this->addRoute($serverName, $name, $handler->route);
                        }
                    }
                }

                foreach ($dynamic as $method => $routes) {
                    foreach ($routes as $route) {
                        foreach ($route['routeMap'] as [$handler, $variables]) {
                            if ($handler instanceof Handler) {
                                $name = $handler->options['name'] ?? $handler->route;
                                $this->addRoute($serverName, $name, $handler->route);
                            }
                        }
                    }
                }
            }
        }
    }

    public function addRoute(string $server, string $name, string $route)
    {
        $this->routes[$server][$name] = $route;
    }

    public function getRoute(string $server, string $name): ?string
    {
        return $this->routes[$server][$name] ?? null;
    }

    public function getPath(string $name, array $variables = [], string $server = 'http')
    {
        $router = $this->factory->getRouter($server);
        $route = $this->getRoute($server, $name);
        if ($route === null) {
            throw new RouteNotFoundException(sprintf('Route name %s is not found in server %s.', $name, $server));
        }

        $result = $router->getRouteParser()->parse($route);
        foreach ($result as $items) {
            $path = '';
            $vars = $variables;
            foreach ($items as $item) {
                if (is_array($item)) {
                    [$key] = $item;
                    if (! isset($vars[$key])) {
                        $path = null;
                        break;
                    }
                    $path .= $vars[$key];
                    unset($vars[$key]);
                } else {
                    $path .= $item;
                }
            }

            if (empty($vars) && $path !== null) {
                return $path;
            }
        }

        throw new RouteInvalidException('Route is invliad.');
    }
}
