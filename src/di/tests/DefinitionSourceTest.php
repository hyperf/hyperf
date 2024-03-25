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
use HyperfTest\Di\Stub\Foo;
use HyperfTest\Di\Stub\FooFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DefinitionSourceTest extends TestCase
{
    public function testAddDefinition()
    {
        $container = new Container(new DefinitionSource([]));
        $container->define('Foo', function () {
            return 'bar';
        });
        $this->assertEquals('bar', $container->get('Foo'));
    }

    public function testDefinitionFactory()
    {
        $container = new Container(new DefinitionSource([]));
        $container->define('Foo', FooFactory::class);

        $foo = $container->get('Foo');
        $this->assertInstanceOf(Foo::class, $foo);
    }
}
