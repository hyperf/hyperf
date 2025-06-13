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

namespace HyperfTest\DbConnection;

use Hyperf\DbConnection\Frequency;
use HyperfTest\DbConnection\Stubs\FrequencyStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FrequencyTest extends TestCase
{
    public function testFreq()
    {
        $freq = new Frequency();
        $freq->hit();
        $freq->hit();
        $freq->hit();
        $freq->hit(2);

        $this->assertSame(5.0, $freq->frequency());

        $freq = new FrequencyStub();
        $freq->hit(3);
        sleep(1);
        $freq->hit(4);
        sleep(1);
        $freq->hit(5);
        sleep(1);
        $freq->hit(9);

        $this->assertSame(3, count($freq->getHits()));
        $this->assertSame(6.0, $freq->frequency());
    }
}
