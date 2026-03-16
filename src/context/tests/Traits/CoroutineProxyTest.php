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

namespace HyperfTest\Context\Traits;

use Hyperf\Context\Context;
use Hyperf\Context\Traits\CoroutineProxy;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CoroutineProxyTest extends TestCase
{
    public function testCoroutineProxy()
    {
        Context::set('bar', new Bar());
        $foo = new Foo();
        $this->assertSame('bar', $foo->callBar());
        $this->assertSame('bar', $foo->bar);
        $foo->bar = 'foo';
        $this->assertSame('foo', $foo->bar);
    }

    public function testCoroutineProxyException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing $proxyKey property in HyperfTest\Context\Traits\Foo2.');
        $foo = new Foo2();
        $foo->callBar();
    }
}

class Bar
{
    public $bar = 'bar';

    public function callBar()
    {
        return 'bar';
    }
}

class Foo
{
    use CoroutineProxy;

    protected $proxyKey = 'bar';
}

class Foo2
{
    use CoroutineProxy;
}
