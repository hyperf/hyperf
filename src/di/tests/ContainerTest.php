<?php

namespace HyperfTest\Di;


use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use HyperfTest\Di\Stub\Foo;
use HyperfTest\Di\Stub\FooInterface;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{

    public function testHas()
    {
        $container = new Container(new DefinitionSource([], [], new Scanner()));
        $this->assertFalse($container->has(FooInterface::class));
        $this->assertFalse($container->has(NotExistClass::class));
        $this->assertTrue($container->has(Foo::class));
    }

}