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

use FastRoute\RouteCollector;

/**
 * Class Router
 * @method static addRoute($httpMethod, $route, $handler)
 * @method static addGroup($prefix, callable $callback)
 * @method static get($route, $handler)
 * @method static post($route, $handler)
 * @method static put($route, $handler)
 * @method static delete($route, $handler)
 * @method static patch($route, $handler)
 * @method static head($route, $handler)
 * @package Hyperf\HttpServer\Router
 */
class Router
{
    protected static $router;

    public static function __callStatic($name, $arguments)
    {
        return static::$router->$name(...$arguments);
    }

    public static function init(RouteCollector $routeCollector)
    {
        static::$router = $routeCollector;
    }
}
