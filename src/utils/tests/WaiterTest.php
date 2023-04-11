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
namespace HyperfTest\Utils;

use Hyperf\Context\ApplicationContext;
use Hyperf\Coroutine\Exception\WaitTimeoutException;
use Hyperf\Engine\Channel;
use Hyperf\Utils\Coroutine;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
class WaiterTest extends TestCase
{
    protected function setUp(): void
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
        $container->shouldReceive('get')->with(\Hyperf\Utils\Waiter::class)->andReturn(new \Hyperf\Utils\Waiter());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testWait()
    {
        $id = uniqid();
        $result = wait(function () use ($id) {
            return $id;
        });

        $this->assertSame($id, $result);

        $id = rand(0, 9999);
        $result = wait(function () use ($id) {
            return $id + 1;
        });

        $this->assertSame($id + 1, $result);
    }

    public function testWaitNone()
    {
        $callback = function () {
        };
        $result = wait($callback);
        $this->assertSame($result, $callback());
        $this->assertSame(null, $result);

        $callback = function () {
            return null;
        };
        $result = wait($callback);
        $this->assertSame($result, $callback());
        $this->assertSame(null, $result);
    }

    public function testWaitException()
    {
        $message = uniqid();
        $callback = function () use ($message) {
            throw new RuntimeException($message);
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        wait($callback);
    }

    public function testWaitReturnException()
    {
        $message = uniqid();
        $callback = function () use ($message) {
            return new RuntimeException($message);
        };

        $result = wait($callback);
        $this->assertInstanceOf(RuntimeException::class, $result);
        $this->assertSame($message, $result->getMessage());
    }

    public function testPushTimeout()
    {
        $channel = new Channel(1);
        $this->assertSame(true, $channel->push(1, 1));
        $this->assertSame(false, $channel->push(1, 1));
    }

    public function testTimeout()
    {
        $callback = function () {
            Coroutine::sleep(0.5);
            return true;
        };

        $this->expectException(WaitTimeoutException::class);
        $this->expectExceptionMessage('Channel wait failed, reason: Timed out for 0.001 s');
        wait($callback, 0.001);
    }
}
