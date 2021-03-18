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

use Hyperf\Di\ReflectionManager;
use HyperfTest\Di\Stub\Ast\Bar;
use HyperfTest\Di\Stub\FooProperty;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ReflectionTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
        ReflectionManager::clear();
    }

    public function testReflectProperties()
    {
        $res = ReflectionManager::reflectPropertyNames(FooProperty::class);

        $this->assertSame(['name', 'nickname', 'username'], $res);
    }

    public function testReturnType()
    {
        $paramaters = ReflectionManager::reflectClass(Bar::class)->getMethod('__construct')->getParameters();
        foreach ($paramaters as $parameter) {
            $this->assertTrue($parameter->getType() instanceof \ReflectionNamedType);
        }

        $return = ReflectionManager::reflectClass(Bar::class)->getMethod('getId')->getReturnType();
        $this->assertTrue($return instanceof \ReflectionNamedType);

        $callback = function (int $id): int {
            return $id + 1;
        };

        $func = new \ReflectionFunction($callback);
        $this->assertTrue($func->getReturnType() instanceof \ReflectionNamedType);

        $paramaters = $func->getParameters();
        foreach ($paramaters as $parameter) {
            $this->assertTrue($parameter->getType() instanceof \ReflectionNamedType);
        }
    }
}
