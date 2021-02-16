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
namespace HyperfTest\Utils\Reflection;

use Hyperf\Utils\Reflection\ClassInvoker;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ClassInvokerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testClassInvokerCall()
    {
        $invoker = new ClassInvoker(new Caller());
        $this->assertSame(1, $invoker->one());
        $this->assertSame(1, $invoker->one());
        $this->assertSame(123, $invoker->two(123));
        $this->assertSame(3, $invoker->three(1, 2));
    }

    public function testClassInvokerCallNotExistMethod()
    {
        /** @var Caller $invoker */
        $invoker = new ClassInvoker(new Caller());

        $this->expectException(\ReflectionException::class);
        $this->expectExceptionMessage('Method zero does not exist');
        $invoker->zero();
    }
}

class Caller
{
    public function three($a, $b)
    {
        return $a + $b;
    }

    protected function two($data)
    {
        return $data;
    }

    private function one()
    {
        return 1;
    }
}
