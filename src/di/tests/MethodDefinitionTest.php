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

use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\ReflectionType;
use HyperfTest\Di\Stub\Foo;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MethodDefinitionTest extends TestCase
{
    public function testGetOrParse()
    {
        $definitions = MethodDefinitionCollector::getOrParse(Foo::class, 'getBar');
        $this->assertSame(4, count($definitions));

        $this->assertArrayNotHasKey('defaultValue', $definitions[0]);
        $this->assertArrayHasKey('defaultValue', $definitions[1]);
    }

    public function testGetParameters()
    {
        $collector = new MethodDefinitionCollector();
        /** @var ReflectionType[] $definitions */
        $definitions = $collector->getParameters(Foo::class, 'getBar');
        $this->assertEquals(4, count($definitions));
        $this->assertEquals('int', $definitions[0]->getName());
        $this->assertFalse($definitions[0]->getMeta('defaultValueAvailable'));
        $this->assertTrue($definitions[1]->getMeta('defaultValueAvailable'));
    }

    public function testGetParameterOfNoType()
    {
        $collector = new MethodDefinitionCollector();
        /** @var ReflectionType[] $definitions */
        $definitions = $collector->getParameters(Foo::class, 'getFoo');
        $this->assertEquals(1, count($definitions));
        $this->assertEquals('mixed', $definitions[0]->getName());
    }

    public function testGetReturnType()
    {
        $collector = new MethodDefinitionCollector();
        /** @var ReflectionType[] $definitions */
        $type = $collector->getReturnType(Foo::class, 'getBar');
        $this->assertEquals('mixed', $type->getName());
    }
}
