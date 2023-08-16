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

use Closure;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Coroutine\Waiter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TimerTest extends TestCase
{
    public function testAfter()
    {
        $this->wait(function () {
            $id = 0;
            $timer = new Timer();
            $identifier = uniqid();
            $timer->after(0.001, function ($isClosing) use (&$id) {
                ++$id;
                $this->assertFalse($isClosing);
            }, $identifier);

            $this->assertSame(0, $id);
            usleep(10000);
            $this->assertSame(1, $id);
        });
    }

    public function testAfterWhenClosing()
    {
        $this->wait(function () {
            $id = 0;
            $timer = new Timer();
            $identifier = uniqid();
            $timer->after(0.001, function ($isClosing) use (&$id) {
                ++$id;
                $this->assertTrue($isClosing);
            }, $identifier);

            $this->assertSame(0, $id);
            CoordinatorManager::until($identifier)->resume();
            $this->assertSame(1, $id);
        });
    }

    public function testAfterWhenClear()
    {
        $this->wait(function () {
            $id = 0;
            $timer = new Timer();
            $identifier = uniqid();
            $ret = $timer->after(0.001, function () use (&$id) {
                ++$id;
            }, $identifier);
            $timer->clear($ret);
            CoordinatorManager::until($identifier)->resume();
            $this->assertSame(0, $id);
        });
    }

    public function testTick()
    {
        $this->wait(function () {
            $id = 0;
            $timer = new Timer();
            $identifier = uniqid();
            $timer->tick(0.001, function () use (&$id) {
                ++$id;
            }, $identifier);
            usleep(10000);
            CoordinatorManager::until($identifier)->resume();
            $this->assertGreaterThanOrEqual(1, $id);
        });
    }

    public function testTickWhenReturnStop()
    {
        $this->wait(function () {
            $id = 0;
            $timer = new Timer();
            $identifier = uniqid();
            $timer->tick(0.001, function () use (&$id) {
                ++$id;
                if ($id >= 10) {
                    return Timer::STOP;
                }
            }, $identifier);
            usleep(20000);
            $this->assertSame(10, $id);
        });
    }

    public function testClearDontExistsClosure()
    {
        $timer = new Timer();

        $timer->clear(999);

        $this->assertTrue(true);
    }

    public function testUntil()
    {
        $this->wait(function () {
            $id = 0;
            $timer = new Timer();
            $identifier = uniqid();
            $timer->until(function () use (&$id) {
                ++$id;
            }, $identifier);

            $this->assertSame(0, $id);
            CoordinatorManager::until($identifier)->resume();
            $this->assertSame(1, $id);
        });
    }

    public function testUntilWhenClear()
    {
        $this->wait(function () {
            $id = 0;
            $timer = new Timer();
            $identifier = uniqid();
            $ret = $timer->until(function () use (&$id) {
                ++$id;
            }, $identifier);
            $timer->clear($ret);
            $this->assertSame(0, $id);
            CoordinatorManager::until($identifier)->resume();
            $this->assertSame(0, $id);
        });
    }

    private function wait(Closure $closure)
    {
        $waiter = new Waiter();
        $waiter->wait($closure);
    }
}
