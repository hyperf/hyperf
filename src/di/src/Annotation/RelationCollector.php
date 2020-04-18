<?php

namespace Hyperf\Di\Annotation;

/**
 * This collector is use to collect the relation of parent class and sub-class, also include the trait and sub-class.
 */
class RelationCollector
{

    /**
     * @var array
     */
    public static $container = [];

    public static function addRelation(string $key, string $className): void
    {
        static::$container[$key][] = $className;
    }

    public static function getRelation(string $key): array
    {
        return static::getContainer()[$key] ?? [];
    }

    public function getContainer(): array
    {
        return static::$container;
    }

}