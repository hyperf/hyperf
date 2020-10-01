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

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
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
     * @var null|ClassReflector
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
            new AutoloadSourceLocator($astLocator, $parser),
        ])));
        return static::$instance;
    }

    public static function reflectClass(string $className, ?ReflectionClass $reflection = null): ReflectionClass
    {
        if (! isset(static::$container['class'][$className])) {
            static::$container['class'][$className] = $reflection ?? static::getClassReflector()->reflect($className);
        }
        return static::$container['class'][$className];
    }

    public static function clear(?string $key = null): void
    {
        if ($key === null) {
            static::$container = [];
            static::$instance = null;
        }
    }
}
