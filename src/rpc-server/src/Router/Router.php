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

namespace Hyperf\RpcServer\Router;

/**
 * @method static addRoute($httpMethod, string $route, $handler, array $options = [])
 * @method static addGroup($prefix, callable $callback)
 */
class Router
{
    /**
     * @var string
     */
    protected static $serverName = 'rpc';

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
        $temp = $serverName;
        static::$serverName = $serverName;
        call($callback);
        static::$serverName = $temp;
        unset($temp);
    }

    public static function init(DispatcherFactory $factory)
    {
        static::$factory = $factory;
    }

    public static function add(string $route, $handler, array $options = [])
    {
        return self::addRoute('GET', $route, $handler, $options);
    }
}
