<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\DbConnection;

use Hyperf\DbConnection\Frequency;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FrequencyTest extends TestCase
{
    public function testFreq()
    {
        $freq = new Frequency();
        $freq->hit();
        $freq->hit();
        $freq->hit();
        $freq->hit(2);

        $this->assertSame(5.0, $freq->freq());

        $freq = new Frequency(2);
        $freq->hit(3);
        sleep(1);
        $freq->hit(4);
        sleep(1);
        $freq->hit(5);
        sleep(1);
        $freq->hit(9);
        $this->assertSame(6.0, $freq->freq());
    }
}
