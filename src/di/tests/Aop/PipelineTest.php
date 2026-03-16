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

namespace HyperfTest\Di\Aop;

use Hyperf\Di\Aop\Pipeline;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use HyperfTest\Di\Stub\Aspect\NoProcessAspect;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use WeakReference;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PipelineTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testWeakReferenceForPipelineCarry()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(NoProcessAspect::class)->andReturn(new NoProcessAspect());
        $pipeline = new Pipeline($container);

        $point = new ProceedingJoinPoint(function () {
        }, 'Foo', 'call', []);

        $res = $pipeline->via('process')->through([
            NoProcessAspect::class,
        ])->send($point)->then(function () {
        });

        $this->assertTrue($res);
        $wr = WeakReference::create($pipeline);
        $wr->get();
        unset($pipeline);
        $this->assertNull($wr->get());
    }
}
