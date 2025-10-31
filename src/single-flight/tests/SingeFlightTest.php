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

use Hyperf\SingleFlight\Exception\RuntimeException;
use Hyperf\SingleFlight\Exception\TimeoutException;
use Hyperf\SingleFlight\SingleFlight;
use PHPUnit\Framework\TestCase;
use Throwable;

use function Hyperf\Coroutine\go;
use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
class SingeFlightTest extends TestCase
{
    public function testSingeFlight()
    {
        $barrierKey = uniqid();
        $ret = [];
        $callables = [];
        $range = range(1, 10000);
        foreach ($range as $v) {
            $callables[] = static function () use ($barrierKey, $v, &$ret) {
                $ret[] = SingleFlight::do($barrierKey, static function () use ($v) {
                    // ensure that other coroutines can be scheduled at the same time
                    usleep(1000);
                    return $v;
                });
            };
        }
        parallel($callables, count($callables));

        $this->assertCount(count($range), $ret);
        $this->assertCount(1, array_unique($ret));
        $this->assertEmpty(SingleFlight::list());
    }

    public function testSingeFlightWithRuntimeException()
    {
        $barrierKey = uniqid();
        $ret = [];
        $callables = [];
        $range = range(1, 10000);
        foreach ($range as $v) {
            $callables[] = static function () use ($barrierKey, $v, &$ret) {
                try {
                    SingleFlight::do($barrierKey, static function () use ($v) {
                        // ensure that other coroutines can be scheduled at the same time
                        usleep(1000);
                        throw new \RuntimeException('from ' . $v);
                    });
                } catch (RuntimeException $e) {
                    $ret[] = $e->getPrevious()->getMessage();
                }
            };
        }
        parallel($callables, count($callables));

        $this->assertCount(count($range), $ret);
        $this->assertCount(1, array_unique($ret));
        $this->assertEmpty(SingleFlight::list());
    }

    public function testSingeFlightWithTimeoutException()
    {
        $barrierKey = uniqid();
        $ret = [];
        $callables = [];
        $range = range(1, 10000);
        foreach ($range as $v) {
            $callables[] = static function () use ($barrierKey, $v, &$ret) {
                try {
                    SingleFlight::do($barrierKey, static function () use ($v, &$ret) {
                        // ensure that other coroutines can be scheduled at the same time
                        usleep(2000 * 1000);
                        $ret[] = $v;
                        return $v;
                    }, 0.1);
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
        $this->assertEmpty(SingleFlight::list());
    }

    public function testSingleFlightWithForgetWithoutWaiters()
    {
        $barrierKey = uniqid();
        $wanted = 'expected';

        $ret = SingleFlight::do($barrierKey, fn () => $wanted);
        $this->assertSame($wanted, $ret);
        $this->assertEmpty(SingleFlight::list());

        SingleFlight::forget($barrierKey);

        $wanted .= '_after_forget';
        $ret = SingleFlight::do($barrierKey, fn () => $wanted);
        $this->assertSame($wanted, $ret);
        $this->assertEmpty(SingleFlight::list());

        $beforeForget = 'before_forget';
        go(function () use ($barrierKey) {
            usleep(500);
            SingleFlight::forget($barrierKey);
        });
        $ret = SingleFlight::do($barrierKey, function () use ($beforeForget) {
            usleep(1000);
            return $beforeForget;
        });
        $this->assertSame($beforeForget, $ret);
        $this->assertEmpty(SingleFlight::list());
    }

    public function testSingleFlightWithForgetWithWaiters()
    {
        $barrierKey = uniqid();
        $ret = [];
        $callables = [];
        $range = range(1, 10000);
        $forgetTimes = 2;
        go(static function () use ($barrierKey, $forgetTimes) {
            for ($i = 0; $i < $forgetTimes; ++$i) {
                usleep(500 * 1000);
                SingleFlight::forget($barrierKey);
            }
        });
        foreach ($range as $v) {
            $callables[] = static function () use ($barrierKey, $v, &$ret) {
                $ret[] = SingleFlight::do($barrierKey, static function () use ($v, &$ret) {
                    // ensure that other coroutines can be scheduled at the same time
                    usleep(2000 * 1000);
                    return $v;
                });
            };
        }
        parallel($callables);

        $this->assertCount(count($range), $ret);
        $this->assertCount($forgetTimes + 1, array_unique($ret));
        $this->assertEmpty(SingleFlight::list());
    }
}
