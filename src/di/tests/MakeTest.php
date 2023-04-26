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

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use HyperfTest\Di\Stub\Bar;
use HyperfTest\Di\Stub\Demo;
use HyperfTest\Di\Stub\Foo;
use PHPUnit\Framework\TestCase;

use function Hyperf\Support\make;

/**
 * @internal
 * @covers \Hyperf\Di\Container
 */
class MakeTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new Container(new DefinitionSource([]));
        ApplicationContext::setContainer($container);
    }

    public function testMakeFunction()
    {
        $this->assertTrue(function_exists('make'));
        $this->assertInstanceOf(Foo::class, $foo = make(Foo::class, [
            'string' => '123',
            'int' => 234,
        ]));
        $this->assertSame('123', $foo->string);
        $this->assertSame(234, $foo->int);

        $this->assertInstanceOf(Foo::class, $foo = make(Foo::class, ['123', 234]));
        $this->assertSame('123', $foo->string);
        $this->assertSame(234, $foo->int);
    }

    public function testMakeIndexedParameters()
    {
        $this->assertTrue(function_exists('make'));
        $this->assertInstanceOf(Foo::class, $foo = make(Foo::class, ['123', 'int' => 234]));
        $this->assertSame('123', $foo->string);
        $this->assertSame(234, $foo->int);

        $this->assertInstanceOf(Foo::class, $foo = make(Foo::class, [1 => 123, 'string' => '123']));
        $this->assertSame('123', $foo->string);
        $this->assertSame(123, $foo->int);
    }

    public function testMakeDependenceParameters()
    {
        $id = uniqid();
        $this->assertInstanceOf(Bar::class, $bar = make(Bar::class, ['id' => $id]));
        $this->assertSame($id, $bar->id);
        $this->assertInstanceOf(Demo::class, $bar->demo);
        $this->assertSame(1, $bar->demo->getId());
        $this->assertNull($bar->name);

        $id = uniqid();
        $this->assertInstanceOf(Bar::class, $bar = make(Bar::class, [$id, 2 => 'Hyperf']));
        $this->assertSame($id, $bar->id);
        $this->assertInstanceOf(Demo::class, $bar->demo);
        $this->assertSame(1, $bar->demo->getId());
        $this->assertSame('Hyperf', $bar->name);
    }
}
