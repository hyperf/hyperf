<?php
namespace Phar\Core;

class Container
{
    private static $container = [];

    public static function get($key){

    }

    public static function set($config){
        foreach ($config['class'] as $key=>$class){
            $ref = new \ReflectionClass($class);
            var_dump($ref->getProperties());
            //die;
            //var_dump(self::$container[$class]->getClassNames());
        }
        var_dump(self::$container);
        die;
        return self::$container;
    }
}