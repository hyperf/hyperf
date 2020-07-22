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

use Hyperf\Utils\Traits\Container;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ContainerTest extends TestCase
{
    public function testGet()
    {
        Foo::set('foo', 1);
        $this->assertNull(Bar::get('foo'));
        Bar::set('foo', 2);
        $this->assertEquals(1, Foo::get('foo'));
        $this->assertEquals(2, Bar::get('foo'));
    }
}

class Foo
{
    use Container;
}
class Bar
{
    use Container;
}
