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

use Hyperf\Coroutine\WaitGroup;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class WaitGroupTest extends TestCase
{
    public function testWaitAgain()
    {
        $wg = new WaitGroup();
        $wg->add(2);
        $result = [];
        $i = 2;
        while ($i--) {
            Coroutine::create(function () use ($wg, &$result) {
                Coroutine::sleep(0.001);
                $result[] = true;
                $wg->done();
            });
        }
        $wg->wait(1);
        $this->assertTrue(count($result) === 2);

        $wg->add();
        $wg->add();
        $result = [];
        $i = 2;
        while ($i--) {
            Coroutine::create(function () use ($wg, &$result) {
                Coroutine::sleep(0.001);
                $result[] = true;
                $wg->done();
            });
        }
        $wg->wait(1);
        $this->assertTrue(count($result) === 2);
    }
}
