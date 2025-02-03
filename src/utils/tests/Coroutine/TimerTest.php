<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Utils\Coroutine;

use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine\Timer;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;

/**
 * @internal
 * @coversNothing
 */
class TimerTest extends TestCase
{
    protected function setUp()
    {
        CoordinatorManager::clear(Constants::WORKER_EXIT);
    }

    protected function tearDown()
    {
        CoordinatorManager::clear(Constants::WORKER_EXIT);
    }

    public function testTimerAfter()
    {
        $timer = new Timer();
        $ms = microtime(true);
        $chan = new Channel(1);
        $id = $timer->after(10, function () use ($chan) {
            $chan->push(microtime(true));
        });

        $this->assertSame(1, $id);
        $this->assertGreaterThan($ms + 0.01, $chan->pop());
    }

    public function testTimerAfterExit()
    {
        $timer = new Timer();
        $chan = new Channel(1);
        $timer->after(10, function () use ($chan) {
            $chan->push(microtime(true));
        });

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();

        $this->assertSame(false, $chan->pop(0.05));
    }

    public function testTimerTick()
    {
        $timer = new Timer();
        $chan = new Channel(5);
        $ms = microtime(true);
        $id = $timer->tick(10, function () use ($chan) {
            $chan->push(microtime(true));
        });

        $this->assertGreaterThan($ms + 0.01, $ms = $chan->pop());
        $this->assertGreaterThan($ms + 0.01, $ms = $chan->pop());

        $timer->clear($id);
    }
}
