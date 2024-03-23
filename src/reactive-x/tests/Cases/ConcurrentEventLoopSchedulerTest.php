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

namespace HyperfTest\Cases;

use Hyperf\Engine\Channel;
use Hyperf\ReactiveX\Observable;
use Hyperf\ReactiveX\RxSwoole;
use Hyperf\ReactiveX\Scheduler\ConcurrentEventLoopScheduler;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Swoole\Runtime;

use function Hyperf\Support\swoole_hook_flags;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ConcurrentEventLoopSchedulerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Runtime::enableCoroutine(swoole_hook_flags());
        RxSwoole::init();
    }

    public function testScheduler()
    {
        $result = new Channel(2);
        $o = Observable::interval(1, new ConcurrentEventLoopScheduler(RxSwoole::getLoop()));
        $o->take(1)->subscribe(
            function ($x) use ($result) {
                usleep(2000);
                $result->push($x);
            }
        );
        $o->skip(1)->take(1)->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $this->assertEquals(1, $result->pop());
        $this->assertEquals(0, $result->pop());
    }
}
