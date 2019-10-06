<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace HyperfTest\Pool;

use HyperfTest\Pool\Stub\FrequencyStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FrequencyTest extends TestCase
{
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
