<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Di;

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Definition\ScanConfig;
use HyperfTest\Di\Stub\Foo;
use HyperfTest\Di\Stub\FooInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ContainerTest extends TestCase
{
    public function testHas()
    {
        $container = new Container(new DefinitionSource([], new ScanConfig()));
        $this->assertFalse($container->has(FooInterface::class));
        $this->assertFalse($container->has(NotExistClass::class));
        $this->assertTrue($container->has(Foo::class));
    }
}
