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

namespace HyperfTest\Di;

use Hyperf\Di\ReflectionManager;
use HyperfTest\Di\Stub\Ast\Bar;
use HyperfTest\Di\Stub\Ast\FooTrait;
use HyperfTest\Di\Stub\Foo;
use HyperfTest\Di\Stub\FooInterface;
use HyperfTest\Di\Stub\Inject\Foo3Trait;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ReflectionTest extends TestCase
{
    public function testReturnType()
    {
        $paramaters = ReflectionManager::reflectClass(Bar::class)->getMethod('__construct')->getParameters();
        foreach ($paramaters as $parameter) {
            $this->assertTrue($parameter->getType() instanceof ReflectionNamedType);
        }

        $return = ReflectionManager::reflectClass(Bar::class)->getMethod('getId')->getReturnType();
        $this->assertTrue($return instanceof ReflectionNamedType);

        $callback = function (int $id): int {
            return $id + 1;
        };

        $func = new ReflectionFunction($callback);
        $this->assertTrue($func->getReturnType() instanceof ReflectionNamedType);

        $paramaters = $func->getParameters();
        foreach ($paramaters as $parameter) {
            $this->assertTrue($parameter->getType() instanceof ReflectionNamedType);
        }
    }

    public function testReflectionPropertyForTraitUseTrait()
    {
        $res = ReflectionManager::reflectPropertyNames(Foo3Trait::class);

        $this->assertSame(['bar', 'foo'], $res);
    }

    public function testReflectionClass()
    {
        $reflection = ReflectionManager::reflectClass(Foo::class);
        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertTrue($reflection->isInstantiable());
        $this->assertSame(ReflectionManager::getContainer()['class'][Foo::class], $reflection);
    }

    public function testReflectionInterface()
    {
        $reflection = ReflectionManager::reflectClass(FooInterface::class);
        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertTrue($reflection->isInterface());
        $this->assertSame(ReflectionManager::getContainer()['class'][FooInterface::class], $reflection);
    }

    public function testReflectionProperty()
    {
        $reflection = ReflectionManager::reflectProperty(Foo::class, 'string');
        $this->assertInstanceOf(ReflectionProperty::class, $reflection);
        $this->assertTrue($reflection->isPublic());
        $this->assertSame(ReflectionManager::getContainer()['property'][Foo::class . '::string'], $reflection);
    }

    public function testReflectionMethod()
    {
        $reflection = ReflectionManager::reflectMethod(Foo::class, 'getFoo');
        $this->assertInstanceOf(ReflectionMethod::class, $reflection);
        $this->assertTrue($reflection->isPublic());
        $this->assertSame(ReflectionManager::getContainer()['method'][Foo::class . '::getFoo'], $reflection);
    }

    public function testReflectionTraitMethod()
    {
        $reflection = ReflectionManager::reflectMethod(FooTrait::class, 'getString');
        $this->assertInstanceOf(ReflectionMethod::class, $reflection);
        $this->assertTrue($reflection->isPublic());
        $this->assertSame(ReflectionManager::getContainer()['method'][FooTrait::class . '::getString'], $reflection);
    }

    public function testReflectionManagerGetAllClasses()
    {
        $reflections = ReflectionManager::getAllClasses([__DIR__ . '/Stub']);
        $this->assertGreaterThan(0, count($reflections));
        $classes = [];
        $interfaces = [];
        $traits = [];
        $enums = [];
        foreach ($reflections as $name => $reflection) {
            $this->assertTrue(class_exists($name) || interface_exists($name) || trait_exists($name) || enum_exists($name));
            $this->assertInstanceOf(ReflectionClass::class, $reflection);
            match (true) {
                enum_exists($name) => $enums[] = $name,
                class_exists($name) => $classes[] = $name,
                interface_exists($name) => $interfaces[] = $name,
                trait_exists($name) => $traits[] = $name,
            };
        }

        $this->assertTrue($classes && $interfaces && $traits && $enums);
    }
}
