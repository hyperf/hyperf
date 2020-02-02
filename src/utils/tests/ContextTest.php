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

namespace HyperfTest\Utils;

use Carbon\Carbon;
use Hyperf\Utils\Context;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\System;
use Swoole\Runtime;

/**
 * @internal
 * @coversNothing
 */
class ContextTest extends TestCase
{
    public function testOverride()
    {
        Context::set('override.id', 1);

        $this->assertSame(2, Context::override('override.id', function ($id) {
            return $id + 1;
        }));

        $this->assertSame(2, Context::get('override.id'));
    }

    public function testGetOrSet()
    {
        Context::set('test.store.id', null);
        $this->assertSame(1, Context::getOrSet('test.store.id', function () {
            return 1;
        }));
        $this->assertSame(1, Context::getOrSet('test.store.id', function () {
            return 2;
        }));

        Context::set('test.store.id', null);
        $this->assertSame(1, Context::getOrSet('test.store.id', 1));
    }

    public function testCancel()
    {
        Context::set(Context::DONE, null);
        $chan = new Channel(1);
        Context::go(function () use ($chan) {
            if (Context::done()) {
                $chan->push(1);
                return;
            }
            $chan->push(2);
        });
        $this->assertEquals(2, $chan->pop());
        Context::cancel();
        Context::go(function () use ($chan) {
            if (Context::done()) {
                $chan->push(1);
                return;
            }
            $chan->push(2);
        });
        $this->assertEquals(1, $chan->pop());
    }

    public function testNestedCancel()
    {
        Context::set(Context::DONE, null);
        $chan = new Channel(1);
        Context::go(function () use ($chan) {
            if (Context::done()) {
                $chan->push(1);
                return;
            }
            usleep(20000);
            System::sleep(1);
            Context::go(function () use ($chan) {
                if (Context::done()) {
                    $chan->push(2);
                    return;
                }
                $chan->push(3);
            });
        });
        usleep(10000);
        Context::cancel();
        $this->assertEquals(2, $chan->pop());
    }

    public function testTimeout()
    {
        Context::set(Context::DONE, null);
        Runtime::enableCoroutine();
        Context::setTimeout(5);
        $chan = new Channel(1);
        Context::go(function () use ($chan) {
            if (Context::done()) {
                $chan->push(1);
                return;
            }
            $chan->push(2);
        });
        $this->assertEquals(2, $chan->pop());
        Context::go(function () use ($chan) {
            usleep(10000);
            if (Context::done()) {
                $chan->push(1);
                return;
            }
            $chan->push(2);
        });
        $this->assertEquals(1, $chan->pop());
    }

    public function testDeadline()
    {
        Context::set(Context::DONE, null);
        $deadline = Carbon::now()->addMillisecond(5);
        Context::setDeadline($deadline);
        $chan = new Channel(1);
        Context::go(function () use ($chan) {
            if (Context::done()) {
                $chan->push(1);
                return;
            }
            $chan->push(2);
        });
        $this->assertEquals(2, $chan->pop());
        usleep(10000);
        Context::go(function () use ($chan) {
            if (Context::done()) {
                $chan->push(1);
                return;
            }
            $chan->push(2);
        });
        $this->assertEquals(1, $chan->pop());
    }
}
