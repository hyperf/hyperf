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

namespace HyperfTest\Coroutine;

use HyperfTest\Coroutine\Stub\Bar;
use HyperfTest\Coroutine\Stub\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ContainerTest extends TestCase
{
    public function testContainerTrait()
    {
        Foo::set('test', $id = uniqid());
        $this->assertSame($id, Foo::get('test'));
        $this->assertFalse(Bar::has('test'));
        $this->assertEmpty(Bar::list());
    }
}
