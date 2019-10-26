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

namespace HyperfTest\Cases;

use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Metric\Timer;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TimerTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testObserveDuration()
    {
        $histogram = Mockery::mock(HistogramInterface::class);
        $histogram->shouldReceive('observe')->once();
        $timer = new Timer($histogram);
        $timer->observeDuration();
        $this->assertTrue(true);
    }

    public function testObserveDurationCalledTwice()
    {
        $histogram = Mockery::mock(HistogramInterface::class);
        $histogram->shouldReceive('observe')->once();
        $timer2 = new Timer($histogram);
        $timer2->observeDuration();
        $timer2->observeDuration();
        $this->assertTrue(true);
    }

    public function testObserveDurationNotCalled()
    {
        $histogram = Mockery::mock(HistogramInterface::class);
        $histogram->shouldReceive('observe')->once();
        $timer3 = new Timer($histogram);
        unset($timer3);
        $this->assertTrue(true);
    }
}
