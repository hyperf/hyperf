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

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Engine\Channel;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;
use HyperfTest\Di\Stub\Bar;
use HyperfTest\Di\Stub\Demo;
use HyperfTest\Di\Stub\Foo;
use HyperfTest\Di\Stub\LoadSleep;
use PHPUnit\Framework\TestCase;

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

    public function testConcurrentLoad()
    {

        if (Coroutine::inCoroutine()) {
            $chan = new Channel(3);

            go(function () use ($chan) {
                $load = ApplicationContext::getContainer()->get(LoadSleep::class);
                $chan->push($load);
            });
            go(function () use ($chan) {
                $load = ApplicationContext::getContainer()->get(LoadSleep::class);
                $chan->push($load);
            });
            go(function () use ($chan) {
                $load = ApplicationContext::getContainer()->get(LoadSleep::class);
                $chan->push($load);
            });
            $obj1 = $chan->pop();
            $obj2 = $chan->pop();
            $obj3 = $chan->pop();
            $this->assertTrue($obj1 === $obj2);
            $this->assertTrue($obj1 === $obj3);
        }
        $this->assertTrue(true);
    }
}
