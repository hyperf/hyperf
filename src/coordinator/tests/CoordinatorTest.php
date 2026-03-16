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

namespace HyperfTest\Coordinator;

use Hyperf\Coordinator\Coordinator;
use Hyperf\Coroutine\WaitGroup;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coroutine\go;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CoordinatorTest extends TestCase
{
    public function testYield()
    {
        $coord = new Coordinator();
        $aborted = $coord->yield(0.001);
        $this->assertFalse($aborted);
    }

    public function testYieldMicroSeconds()
    {
        $coord = new Coordinator();
        $aborted = $coord->yield(0.000001);
        $this->assertFalse($aborted);
    }

    public function testYieldResume()
    {
        $coord = new Coordinator();
        $wg = new WaitGroup();
        $wg->add();
        go(function () use ($coord, $wg) {
            $aborted = $coord->yield(10);
            $this->assertTrue($aborted);
            $wg->done();
        });
        $wg->add();
        go(function () use ($coord, $wg) {
            $aborted = $coord->yield(10);
            $this->assertTrue($aborted);
            $wg->done();
        });
        $coord->resume();
        $wg->wait();
    }
}
