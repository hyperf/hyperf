<?php

namespace HyperfTest\Utils;

use Hyperf\Utils\Reflection\ClassInvoker;
use PHPUnit\Framework\TestCase;

class ClassInvokerTest extends TestCase
{
    public function testPrototype()
    {
        $class = new class () {
            protected int $bar = 1;
            private int $foo = 2;
            public int $three = 3;
        };

        $proxyClass = new ClassInvoker($class);

        $this->assertEquals(1, $proxyClass->bar);
        $this->assertEquals(2, $proxyClass->foo);
        $this->assertEquals(3, $proxyClass->three);

        $proxyClass->bar = 0;
        $proxyClass->foo = 0;
        $proxyClass->three = 0;
        $this->assertEquals(0, $proxyClass->bar);
        $this->assertEquals(0, $proxyClass->foo);
        $this->assertEquals(0, $proxyClass->three);
    }

    public function testMethod()
    {
        $class = new class () {
            private function bar(int $bar)
            {
                return $bar;
            }

            protected function foo(int $foo)
            {
                return $foo;
            }
        };
        $proxyClass = new ClassInvoker($class);

        $this->assertEquals(1, $proxyClass->bar(1));
        $this->assertEquals(2, $proxyClass->foo(2));
    }
}