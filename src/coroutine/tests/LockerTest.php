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

use Hyperf\Coroutine\Locker;
use Hyperf\Engine\Channel;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coroutine\go;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class LockerTest extends TestCase
{
    public function testLockAndUnlock()
    {
        $chan = new Channel(10);
        go(function () use ($chan) {
            Locker::lock('foo');
            $chan->push(1);
            usleep(10000);
            $chan->push(2);
            Locker::unlock('foo');
        });

        go(function () use ($chan) {
            Locker::lock('foo');
            $chan->push(3);
            usleep(10000);
            $chan->push(4);
        });

        go(function () use ($chan) {
            Locker::lock('foo');
            $chan->push(5);
            $chan->push(6);
        });

        $ret = [];
        while ($res = $chan->pop(1)) {
            $ret[] = $res;
        }

        $this->assertSame([1, 2, 3, 5, 6, 4], $ret);
    }
}
