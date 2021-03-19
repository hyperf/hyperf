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
namespace Hyperf\Di;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class ReflectionManager extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function reflectClass(string $className): ReflectionClass
    {
        if (! isset(static::$container['class'][$className])) {
            if (! class_exists($className) && ! interface_exists($className) && ! trait_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['class'][$className] = new ReflectionClass($className);
        }
        return static::$container['class'][$className];
    }

    public static function reflectMethod(string $className, string $method): ReflectionMethod
    {
        $key = $className . '::' . $method;
        if (! isset(static::$container['method'][$key])) {
            // TODO check interface_exist
            if (! class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['method'][$key] = static::reflectClass($className)->getMethod($method);
        }
        return static::$container['method'][$key];
    }

    public static function reflectProperty(string $className, string $property): ReflectionProperty
    {
        $key = $className . '::' . $property;
        if (! isset(static::$container['property'][$key])) {
            if (! class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['property'][$key] = static::reflectClass($className)->getProperty($property);
        }
        return static::$container['property'][$key];
    }

    public static function reflectPropertyNames(string $className, ?int $exclude = null)
    {
        $key = $className;
        if (! isset(static::$container['property_names'][$key])) {
            if (! class_exists($className) && ! interface_exists($className) && ! trait_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['property_names'][$key] = [];
            foreach (static::reflectClass($className)->getProperties() as $property) {
                $propertyModifier = $property->getModifiers();
                $level = ReflectionProperty::IS_PUBLIC;
                do {
                    if ($propertyModifier & $level) {
                        static::$container['property_names'][$key][$level][] = $property->getName();
                    }
                } while (($propertyModifier = $propertyModifier & ~$level) && $level = $level << 1);
            }
        }
        $filter = [];
        if ($exclude) {
            foreach (static::$container['property_names'][$key] as $level => $propertyNames) {
                if ($exclude & $level) {
                    $filter = array_merge($filter, $propertyNames);
                }
            }
        }
        return array_unique(array_diff(array_merge(...static::$container['property_names'][$key]), $filter));
    }

    public static function clear(?string $key = null): void
    {
        if ($key === null) {
            static::$container = [];
        }
    }
}
