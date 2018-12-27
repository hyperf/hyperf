<?php

namespace Hyperf\Di\Annotation;


use Hyperf\Di\MetadataCollector;

class AnnotationCollector extends MetadataCollector
{

    /**
     * @var array
     */
    protected static $container = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$class]['_c'][$annotation] = $value;
    }

    public static function collectProperty(string $class, string $property, string $annotation, $value): void
    {
        static::$container[$class]['_p'][$property][$annotation] = $value;
    }

}