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
namespace HyperfTest\Support;

use Hyperf\Support\Backoff;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Hyperf\Support\Backoff
 */
class BackoffTest extends TestCase
{
    public function testBackoff()
    {
        $backoff = new Backoff(1);
        $backoff->sleep();
        $firstTick = $backoff->nextBackoff();
        $this->assertGreaterThanOrEqual(1, $firstTick);
        $this->assertLessThanOrEqual(3, $firstTick);
        $backoff->sleep();
        $secondTick = $backoff->nextBackoff();
        $this->assertGreaterThanOrEqual(1, $secondTick);
        $this->assertLessThanOrEqual(3 * $firstTick, $secondTick);
    }
}
