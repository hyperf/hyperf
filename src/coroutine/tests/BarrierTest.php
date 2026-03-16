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

use Hyperf\Coroutine\Barrier;
use Hyperf\Coroutine\Coroutine;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class BarrierTest extends TestCase
{
    public function testBarrier()
    {
        $barrier = Barrier::create();
        $N = 10;
        $count = 0;
        for ($i = 0; $i < $N; ++$i) {
            Coroutine::create(function () use (&$count, $barrier) {
                isset($barrier);
                sleep(1);
                ++$count;
            });
        }
        Barrier::wait($barrier);
        $this->assertSame($N, $count);
    }
}
