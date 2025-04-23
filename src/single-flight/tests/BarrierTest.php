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
use Hyperf\SingleFlight\Exception\TimeoutException;
use PHPUnit\Framework\TestCase;
use Throwable;

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

    public function testBarrierWithRuntimeException()
    {
        $barrierKey = uniqid();
        $ret = [];
        $callables = [];
        $range = range(1, 100);
        foreach ($range as $v) {
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

    public function testBarrierWithTimeoutException()
    {
        $barrierKey = uniqid();
        $ret = [];
        $callables = [];
        $range = range(1, 100);
        foreach ($range as $v) {
            $callables[] = static function () use ($barrierKey, $v, &$ret) {
                try {
                    Barrier::yield($barrierKey, static function () use ($v, &$ret) {
                        // ensure that other coroutines can be scheduled at the same time
                        usleep(700 * 1000);
                        $ret[] = $v;
                        return $v;
                    }, 0.6);
                } catch (Throwable $e) {
                    if ($e instanceof TimeoutException) {
                        $ret[] = $e->getMessage();
                    }
                }
            };
        }
        parallel($callables, count($callables));

        $this->assertCount(count($range), $ret);
        $this->assertCount(2, array_unique($ret));

        $iRet = array_filter($ret, static fn ($v) => is_int($v));
        $this->assertCount(1, $iRet);
    }
}
