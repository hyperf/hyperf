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
namespace Hyperf\HttpServer\Router;

use Hyperf\HttpServer\Exception\Http\RequestNotFoundException;
use Hyperf\HttpServer\Exception\Http\RouteNotFoundException;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Route
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DispatcherFactory
     */
    protected $factory;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->factory = $container->get(DispatcherFactory::class);
    }

    public function getPath(string $name, array $variables = [], string $server = 'http')
    {
        $router = $this->factory->getRouter($server);
        $route = RouteNameCollector::get($server, $name);
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

        throw new RouteNotFoundException('Route is invliad.');
    }

    public function getName(): string
    {
        $dispatched = $this->getRequest()->getAttribute(Dispatched::class);
        if (! $dispatched instanceof Dispatched) {
            throw new RequestNotFoundException('Request is invalid.');
        }

        $handler = $dispatched->handler;
        return $handler->options['name'] ?? $handler->route;
    }

    protected function getRequest(): ServerRequestInterface
    {
        $request = Context::get(ServerRequestInterface::class);
        if (! $request instanceof ServerRequestInterface) {
            throw new RequestNotFoundException('Request is not found.');
        }
        return $request;
    }
}
