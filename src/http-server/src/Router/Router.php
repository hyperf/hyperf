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

/**
 * @method static addRoute($httpMethod, $route, $handler)
 * @method static addGroup($prefix, callable $callback)
 * @method static get($route, $handler)
 * @method static post($route, $handler)
 * @method static put($route, $handler)
 * @method static delete($route, $handler)
 * @method static patch($route, $handler)
 * @method static head($route, $handler)
 */
class Router
{
    /**
     * @var string
     */
    protected static $serverName = 'http';

    /**
     * @var DispatcherFactory
     */
    protected static $factory;

    public static function __callStatic($name, $arguments)
    {
        $router = static::$factory->getRouter(static::$serverName);
        return $router->$name(...$arguments);
    }

    public static function addServer(string $serverName, callable $callback)
    {
        static::$serverName = $serverName;
        call($callback);
        static::$serverName = 'http';
    }

    public static function init(DispatcherFactory $factory)
    {
        static::$factory = $factory;
    }
}
