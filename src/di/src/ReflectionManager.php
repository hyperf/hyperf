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

    public static function reflectPropertyNames(string $className)
    {
        $key = $className;
        if (! isset(static::$container['property_names'][$key])) {
            if (! class_exists($className) && ! interface_exists($className) && ! trait_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['property_names'][$key] = value(function () use ($className) {
                $properties = static::reflectClass($className)->getProperties();
                $result = [];
                foreach ($properties as $property) {
                    $result[] = $property->getName();
                }
                return $result;
            });
        }
        return static::$container['property_names'][$key];
    }

    public static function clear(?string $key = null): void
    {
        if ($key === null) {
            static::$container = [];
        }
    }
}
