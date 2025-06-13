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

namespace HyperfTest\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Pool\Channel;
use Hyperf\Pool\Pool;
use HyperfTest\Pool\Stub\ConstantFrequencyStub;
use HyperfTest\Pool\Stub\FrequencyStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FrequencyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testFrequencyHit()
    {
        $frequency = new FrequencyStub();
        $now = time();
        $frequency->setBeginTime($now - 4);
        $frequency->setHits([
            $now => 1,
            $now - 1 => 10,
            $now - 2 => 10,
            $now - 3 => 10,
            $now - 4 => 10,
        ]);

        $num = $frequency->frequency();
        $this->assertSame(41 / 5, $num);

        $frequency->hit();
        $num = $frequency->frequency();
        $this->assertSame(42 / 5, $num);
    }

    public function testConstantFrequency()
    {
        $pool = Mockery::mock(Pool::class);
        $channel = new Channel(100);
        $pool->shouldReceive('flushOne')->andReturnUsing(function () use ($channel) {
            $channel->push(Mockery::mock(ConnectionInterface::class));
        });

        $stub = new ConstantFrequencyStub($pool);
        Coroutine::sleep(0.005);
        $stub->clear();
        $this->assertGreaterThan(0, $channel->length());
    }

    public function testFrequencyHitOneSecondAfter()
    {
        $frequency = new FrequencyStub();
        $now = time();

        $frequency->setBeginTime($now - 4);
        $frequency->setHits([
            $now => 1,
            $now - 1 => 10,
            $now - 2 => 10,
            $now - 4 => 10,
        ]);
        $num = $frequency->frequency();
        $this->assertSame(31 / 5, $num);
        $frequency->hit();
        $num = $frequency->frequency();
        $this->assertSame(32 / 5, $num);

        $frequency->setHits([
            $now => 1,
            $now - 1 => 10,
            $now - 2 => 10,
            $now - 3 => 10,
        ]);
        $num = $frequency->frequency();
        $this->assertSame(31 / 5, $num);
        $frequency->hit();
        $num = $frequency->frequency();
        $this->assertSame(32 / 5, $num);
    }
}
