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
 * @method static addRoute($httpMethod, string $route, $handler, array $options = [])
 * @method static addGroup($prefix, callable $callback)
 * @method static get($route, $handler, array $options = [])
 * @method static post($route, $handler, array $options = [])
 * @method static put($route, $handler, array $options = [])
 * @method static delete($route, $handler, array $options = [])
 * @method static patch($route, $handler, array $options = [])
 * @method static head($route, $handler, array $options = [])
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
        return $router->{$name}(...$arguments);
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
