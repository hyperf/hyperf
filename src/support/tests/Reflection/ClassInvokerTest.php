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
namespace HyperfTest\Support\Reflection;

use Hyperf\Support\Reflection\ClassInvoker;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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

    public function testClassInvokerGet()
    {
        $invoker = new ClassInvoker(new Caller());
        $this->assertSame(null, $invoker->id);
        $this->assertSame(md5(''), $invoker->md5);
        $this->assertSame(sha1(''), $invoker->sha);

        $invoker = new ClassInvoker(new Caller($id = uniqid()));
        $this->assertSame($id, $invoker->id);
        $this->assertSame(md5($id), $invoker->md5);
        $this->assertSame(sha1($id), $invoker->sha);
    }

    public function testClassInvokerCallNotExistMethod()
    {
        $invoker = new ClassInvoker(new Caller());

        $this->expectException(ReflectionException::class);
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $this->expectExceptionMessage('Method HyperfTest\Support\Reflection\Caller::zero() does not exist');
        } else {
            $this->expectExceptionMessage('Method zero does not exist');
        }
        $invoker->zero();
    }

    public function testClassInvokerGetNotExistProperty()
    {
        $invoker = new ClassInvoker(new Caller());

        $this->expectException(ReflectionException::class);
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $this->expectExceptionMessage('Property HyperfTest\Support\Reflection\Caller::$zero does not exist');
        } else {
            $this->expectExceptionMessage('Property zero does not exist');
        }
        $invoker->zero;
    }
}

class Caller
{
    public $id;

    protected $sha;

    private $md5;

    public function __construct($id = null)
    {
        $this->id = $id;
        $this->md5 = md5($id ?? '');
        $this->sha = sha1($id ?? '');
    }

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
