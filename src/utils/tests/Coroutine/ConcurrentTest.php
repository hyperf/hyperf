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

namespace HyperfTest\Utils\Coroutine;

use Hyperf\Utils\Coroutine\Concurrent;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

/**
 * @internal
 * @coversNothing
 */
class ConcurrentTest extends TestCase
{
    public function testConcurrent()
    {
        $con = new Concurrent(10, 1);
        $count = 0;
        for ($i = 0; $i < 15; ++$i) {
            $con->call(function () use (&$count) {
                Coroutine::sleep(0.1);
                ++$count;
            });
        }

        $this->assertSame(5, $count);
        $this->assertSame(10, $con->length());
    }

    public function testException()
    {
        $con = new Concurrent(10, 1);
        $count = 0;
        for ($i = 0; $i < 15; ++$i) {
            $con->call(function () use (&$count) {
                Coroutine::sleep(0.1);
                ++$count;
                throw new \Exception('ddd');
            });
        }

        $this->assertSame(5, $count);
        $this->assertSame(10, $con->length());
    }
}
