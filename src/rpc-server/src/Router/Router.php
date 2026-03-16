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

namespace Hyperf\RpcServer\Router;

/**
 * @method static void addRoute(string $route, $handler, array $options = [])
 * @method static void addGroup($prefix, callable $callback)
 */
class Router
{
    protected static string $serverName = 'rpc';

    protected static ?DispatcherFactory $factory = null;

    public static function __callStatic($name, $arguments)
    {
        $router = static::$factory->getRouter(static::$serverName);
        return $router->{$name}(...$arguments);
    }

    public static function addServer(string $serverName, callable $callback)
    {
        $temp = $serverName;
        static::$serverName = $serverName;
        $callback();
        static::$serverName = $temp;
        unset($temp);
    }

    public static function init(DispatcherFactory $factory)
    {
        static::$factory = $factory;
    }

    public static function add(string $route, $handler, array $options = []): void
    {
        static::addRoute($route, $handler, $options);
    }
}
