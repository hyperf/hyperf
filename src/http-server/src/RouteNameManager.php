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
namespace Hyperf\HttpServer;

class RouteNameManager
{
    /**
     * @var array
     */
    public static $container = [];

    public static function addName(string $server, string $path, string $method, string $name): void
    {
        if( ! $name ){
            return ;
        }
        if( isset( static::$container[$server]['name_route'][$name] ) ){
            if( static::$container[$server]['name_route'][$name]['path'] == $path ){
                return ;
            }
            throw new \RuntimeException("
                {$name} have already existed ,
                path:".static::$container[$server]['name_route'][$name]['path'].",
                method:".static::$container[$server]['name_route'][$name]['method']
            );
        }
        $method = strtoupper($method);
        static::$container[$server]['route_name'][$path][$method] = $name;
		static::$container[$server]['name_route'][$name] = ['path'=>$path,'method'=>$method];
    }
	public static function getByName(string $server, string $name) :? array
	{
		return static::$container[$server]['name_route'][$name] ?? null;
	}

    public static function getByRoute(string $server, string $rule, string $method) :? string
    {
        $method = strtoupper($method);
        return static::$container[$server]['route_name'][$rule][$method] ?? null;
    }
}
