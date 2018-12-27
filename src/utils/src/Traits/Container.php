<?php

namespace Hyperflex\Utils\Traits;


trait Container
{

    /**
     * @var array
     */
    protected static $container = [];

    /**
     * @inheritdoc
     */
    public static function set($id, $value)
    {
        static::$container[$id] = $value;
    }

    /**
     * @inheritdoc
     */
    public static function get($id)
    {
        return static::$container[$id];
    }

    /**
     * @inheritdoc
     */
    public static function has($id)
    {
        return isset(static::$container[$id]);
    }

}