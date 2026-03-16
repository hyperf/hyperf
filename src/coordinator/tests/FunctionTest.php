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

use Hyperf\Coroutine\WaitGroup;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coordinator\block;
use function Hyperf\Coordinator\resume;
use function Hyperf\Coroutine\go;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FunctionTest extends TestCase
{
    public function testBlock()
    {
        $aborted = block(0.001);
        $this->assertFalse($aborted);
    }

    public function testBlockMicroSeconds()
    {
        $aborted = block(0.000001);
        $this->assertFalse($aborted);
    }

    public function testResume()
    {
        $identifier = uniqid();
        $wg = new WaitGroup();
        $wg->add();
        go(function () use ($wg, $identifier) {
            $aborted = block(10, $identifier);
            $this->assertTrue($aborted);
            $wg->done();
        });
        $wg->add();
        go(function () use ($wg, $identifier) {
            $aborted = block(10, $identifier);
            $this->assertTrue($aborted);
            $wg->done();
        });
        resume($identifier);
        $wg->wait();
    }
}
