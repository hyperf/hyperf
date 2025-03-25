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

use Hyperf\Coroutine\WaitGroup;
use Hyperf\SingleFlight\Barrier;
use Hyperf\SingleFlight\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use function Hyperf\Coroutine\go;

class BarrierTest extends TestCase
{
    public function testBarrier()
    {
        $ret = [];
        $barrierKey = uniqid();
        $wg = new WaitGroup(3);
        go(function () use (&$ret, $wg, $barrierKey) {
            $ret[] = Barrier::yield($barrierKey, static function () {
                // ensure that other two coroutines can be scheduled at the same time
                usleep(100);
                return 1;
            });
            $wg->done();
        });
        go(function () use (&$ret, $wg, $barrierKey) {
            $ret[] = Barrier::yield($barrierKey, static function () {
                usleep(100);
                return 2;
            });
            $wg->done();
        });
        go(function () use (&$ret, $wg, $barrierKey) {
            $ret[] = Barrier::yield($barrierKey, static function () {
                usleep(100);
                return 3;
            });
            $wg->done();
        });
        $wg->wait();
        $this->assertCount(1, array_unique($ret));
    }

    public function testBarrierWithException()
    {
        $ret = [];
        $barrierKey = uniqid();
        $wg = new WaitGroup();
        go(function () use (&$ret, $wg, $barrierKey) {
            $wg->add();
            try {
                Barrier::yield($barrierKey, static function () {
                    // ensure that other two coroutines can be scheduled at the same time
                    usleep(100);
                    throw new \RuntimeException("from 1");
                });
            } catch (\RuntimeException $e) {
                if ($e instanceof RuntimeException) {
                    $ret[] = $e->getPrevious()->getMessage();
                } else {
                    $ret[] = $e->getMessage();
                }
            } finally {
                $wg->done();
            }
        });
        go(function () use (&$ret, $wg, $barrierKey) {
            $wg->add();
            try {
                Barrier::yield($barrierKey, static function () {
                    usleep(100);
                    throw new \RuntimeException("from 2");
                });
            } catch (\RuntimeException $e) {
                if ($e instanceof RuntimeException) {
                    $ret[] = $e->getPrevious()->getMessage();
                } else {
                    $ret[] = $e->getMessage();
                }
            } finally {
                $wg->done();
            }
        });
        go(function () use (&$ret, $wg, $barrierKey) {
            $wg->add();
            try {
                Barrier::yield($barrierKey, static function () {
                    usleep(100);
                    throw new \RuntimeException("from 3");
                });
            } catch (\RuntimeException $e) {
                if ($e instanceof RuntimeException) {
                    $ret[] = $e->getPrevious()->getMessage();
                } else {
                    $ret[] = $e->getMessage();
                }
            } finally {
                $wg->done();
            }
        });
        $wg->wait();
        $this->assertCount(1, array_unique($ret));
    }
}