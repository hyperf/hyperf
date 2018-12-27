<?php

namespace Hyperflex\Di\Annotation;


use Hyperflex\Di\MetadataCollector;

class AspectCollector extends MetadataCollector
{

    /**
     * @var array
     */
    protected static $container = [];

    public static function setBefore(string $aspect, array $classes, array $annotations)
    {
        $before = static::get('before');
        $before['classes'][$aspect] = array_replace($before['classes'][$aspect] ?? [], $classes);
        $before['annotations'][$aspect] = array_replace($before['annotations'][$aspect] ?? [], $annotations);
        static::set('before', $before);
    }

    public static function setArround(string $aspect, array $classes, array $annotations)
    {
        $arround = static::get('arround');
        $arround['classes'][$aspect] = array_replace($arround['classes'][$aspect] ?? [], $classes);
        $arround['annotations'][$aspect] = array_replace($arround['annotations'][$aspect] ?? [], $annotations);
        static::set('arround', $arround);
    }
}