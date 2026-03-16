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

use Hyperf\Di\Aop\ProceedingJoinPoint;
use HyperfTest\Di\Stub\ProxyTraitObject;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ProceedingJoinPointTest extends TestCase
{
    public function testProcessOriginalMethod()
    {
        $obj = new ProceedingJoinPoint(
            fn () => 1,
            ProxyTraitObject::class,
            'incr',
            ['keys' => []]
        );

        $this->assertSame(1, $obj->processOriginalMethod());
    }

    public function testGetArguments()
    {
        $obj = new ProceedingJoinPoint(
            fn () => 1,
            ProxyTraitObject::class,
            'incr',
            ['keys' => []]
        );
        $this->assertSame([], $obj->getArguments());

        $obj = new ProceedingJoinPoint(
            fn () => 1,
            ProxyTraitObject::class,
            'get4',
            ['order' => ['id', 'variadic'], 'keys' => ['id' => 1, 'variadic' => []], 'variadic' => 'variadic']
        );
        $this->assertSame([1], $obj->getArguments());

        $obj = new ProceedingJoinPoint(
            fn () => 1,
            ProxyTraitObject::class,
            'get4',
            ['order' => ['id', 'variadic'], 'keys' => ['id' => 1, 'variadic' => [2, 'foo' => 3]], 'variadic' => 'variadic']
        );
        $this->assertSame([1, 2, 'foo' => 3], $obj->getArguments());

        $obj = new ProceedingJoinPoint(
            fn () => 1,
            ProxyTraitObject::class,
            'get4',
            ['order' => ['id', 'variadic'], 'keys' => ['id' => 1, 'variadic' => [2, 'foo' => 3]], 'variadic' => '']
        );
        $this->assertSame([1, [2, 'foo' => 3]], $obj->getArguments());
    }
}
