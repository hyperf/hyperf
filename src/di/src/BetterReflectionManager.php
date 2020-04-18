<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\EvaledCodeSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;

class BetterReflectionManager extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    /**
     * @var ClassReflector
     */
    protected static $instance;

    public static function getClassReflector(): ClassReflector
    {
        if (! static::$instance) {
            throw new \RuntimeException('The class reflector object does not init yet');
        }
        return static::$instance;
    }

    public static function initClassReflector(array $paths): ClassReflector
    {
        $reflection = new BetterReflection();
        $astLocator = $reflection->astLocator();
        $stubber = $reflection->sourceStubber();
        $parser = $reflection->phpParser();
        static::$instance = new ClassReflector(new MemoizingSourceLocator(new AggregateSourceLocator([
            new DirectoriesSourceLocator($paths, $astLocator),
            new PhpInternalSourceLocator($astLocator, $stubber),
            new EvaledCodeSourceLocator($astLocator, $stubber),
            new AutoloadSourceLocator($astLocator, $parser)
        ])));
        return static::$instance;
    }

    public static function reflectClass(string $className): ReflectionClass
    {
        if (! isset(static::$container['class'][$className])) {
            static::$container['class'][$className] = static::getClassReflector()->reflect($className);
        }
        return static::$container['class'][$className];
    }

    public static function reflectMethod(string $className, string $method): ReflectionMethod
    {
        $key = $className . '::' . $method;
        if (! isset(static::$container['method'][$key])) {
            $reflectionClass = static::reflectClass($className);
            $methods = $reflectionClass->getImmediateMethods();
            static::$container['method'][$key] = $methods($method);
        }
        return static::$container['method'][$key];
    }

    public static function reflectProperty(string $className, string $property): ReflectionProperty
    {
        $key = $className . '::' . $property;
        if (! isset(static::$container['property'][$key])) {
            $reflectionClass = static::reflectClass($className);
            $properties = $reflectionClass->getImmediateProperties();
            static::$container['property'][$key] = $properties[$property];
        }
        return static::$container['property'][$key];
    }

    public static function clear(): void
    {
        static::$container = [];
    }
}
