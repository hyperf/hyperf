<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

class MiddlewareManager
{
    /**
     * @var array
     */
    public static $container = [];

    public static function addMiddleware(string $server, $path, string $method, string $middleware): void
    {
        $method = strtoupper($method);
        static::$container[$server][static::handlePath($path)][$method][] = $middleware;
    }

    public static function addMiddlewares(string $server, $path, string $method, array $middlewares): void
    {
        $method = strtoupper($method);
        foreach ($middlewares as $middleware) {
            static::$container[$server][static::handlePath($path)][$method][] = $middlewares;
        }
    }

    public static function get(string $server, string $rule, string $method): array
    {
        $method = strtoupper($method);
        foreach(static::$container[$server] as $key => $value){
            if ($rule == $key){
                return $value[$method] ?? [];
            } elseif(strstr($key, '.*?') !== false && preg_match($key, $rule)) {
                return $value[$method] ?? [];
            }
        }

        return [];
    }

    public static function handlePath($path) : string
    {
        if (is_string($path)){
            return $path;
        } elseif (is_array($path)){
            if(count($path) == 1){
                return $path[0];
            } else {
                $temp_path = array();
                foreach($path as $sub_path){
                    if (is_string($sub_path)){
                        $temp_sub_path = explode('/', trim($sub_path, '/'));
                        foreach($temp_sub_path as $item){
                            $temp_path[] = $item;
                        }
                    } elseif(is_array($sub_path)) {
                        $temp_path[] = '.*?';
                    }
                }
                $temp_path_str = '/'.implode('\/', $temp_path).'/';
                return $temp_path_str;
            }
        }
    }
}
