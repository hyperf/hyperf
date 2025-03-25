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

namespace HyperfTest\SingleFlight;

use Hyperf\SingleFlight\Barrier;
use Hyperf\SingleFlight\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
class BarrierTest extends TestCase
{
    public function testBarrier()
    {
        $barrierKey = uniqid();
        $ret = [];
        $callables = [];
        $range = range(1, 100);
        foreach ($range as $v) {
            $callables[] = static function () use ($barrierKey, $v, &$ret) {
                $ret[] = Barrier::yield($barrierKey, static function () use ($v) {
                    // ensure that other coroutines can be scheduled at the same time
                    usleep(1000);
                    return $v;
                });
            };
        }
        parallel($callables, count($callables));

        $this->assertCount(count($range), $ret);
        $this->assertCount(1, array_unique($ret));
    }

    public function testBarrierWithException()
    {
        $barrierKey = uniqid();
        $ret = [];
        $callables = [];
        $range = range(1, 100);
        foreach (range(1, 100) as $v) {
            $callables[] = static function () use ($barrierKey, $v, &$ret) {
                try {
                    Barrier::yield($barrierKey, static function () use ($v) {
                        // ensure that other coroutines can be scheduled at the same time
                        usleep(1000);
                        throw new \RuntimeException('from ' . $v);
                    });
                } catch (\RuntimeException $e) {
                    if ($e instanceof RuntimeException) {
                        $ret[] = $e->getPrevious()->getMessage();
                    } else {
                        $ret[] = $e->getMessage();
                    }
                }
            };
        }
        parallel($callables, count($callables));

        $this->assertCount(count($range), $ret);
        $this->assertCount(1, array_unique($ret));
    }
}
