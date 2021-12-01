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
namespace HyperfTest\Utils\Traits;

use Hyperf\Utils\Context;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StaticInstanceTest extends TestCase
{
    public function testGetInstance()
    {
        $id = uniqid();
        $foo = wait(function () use ($id) {
            $this->assertTrue(Context::get(FooContext::class) === null);
            $foo = FooContext::instance([$id]);
            $this->assertSame($id, $foo->id());
            $this->assertSame($foo, FooContext::instance());
            $this->assertSame($foo, FooContext::instance([]));
            $this->assertTrue(Context::get(FooContext::class) !== null);
            return $foo;
        });

        wait(function () use ($foo) {
            $this->assertNotEquals($foo, $foo = FooContext::instance([]));
            $this->assertSame($foo, FooContext::instance([]));
            $this->assertNotSame($foo, FooContext::instance([], true));
            $this->assertNotSame($foo, FooContext::instance());
        });

        wait(function () use ($id) {
            $this->assertTrue(Context::get(FooContext::class) === null);
            $foo = FooContext::instance([$id]);
            $this->assertSame($foo, FooContext::instance([], false));
            $this->assertTrue(Context::get(FooContext::class) !== null);
            $this->assertTrue(Context::get(FooContext::class . 'foo') === null);
            $this->assertNotSame($foo, FooContext::instance([], false, 'foo'));
            $this->assertTrue(Context::get(FooContext::class . 'foo') !== null);
        });
    }
}

class FooContext
{
    use \Hyperf\Utils\Traits\StaticInstance;

    public function __construct(private string $id = '')
    {
    }

    public function id(): string
    {
        return $this->id;
    }
}
