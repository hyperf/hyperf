<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Di;

use HyperfTest\Di\Stub\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Hyperf\Di\Container
 */
class MakeTest extends TestCase
{
    public function testMakeFunction()
    {
        $this->assertTrue(function_exists('make'));
        $this->assertInstanceOf(Foo::class, $foo = make(Foo::class, [
            'string' => '123',
            'int' => 123,
        ]));
        $this->assertSame('123', $foo->string);
        $this->assertSame(123, $foo->int);

        $this->assertInstanceOf(Foo::class, $foo = make(Foo::class, ['123', 123]));
        $this->assertSame('123', $foo->string);
        $this->assertSame(123, $foo->int);
    }
}
