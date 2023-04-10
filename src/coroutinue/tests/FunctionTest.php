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

use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use PHPUnit\Framework\TestCase;
use Swoole\Runtime;

use function Hyperf\Coroutine\defer;
use function Hyperf\Coroutine\go;
use function Hyperf\Coroutine\parallel;
use function Hyperf\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
class FunctionTest extends TestCase
{
    public function testReturnOfGo()
    {
        $uniqid = uniqid();
        $id = go(function () use (&$uniqid) {
            $uniqid = 'Hyperf';
        });

        $this->assertTrue(is_int($id));
        $this->assertSame('Hyperf', $uniqid);
    }

    /**
     * @group NonCoroutine
     */
    public function testRun()
    {
        $asserts = [
            SWOOLE_HOOK_ALL,
            SWOOLE_HOOK_SLEEP,
            SWOOLE_HOOK_CURL,
        ];

        foreach ($asserts as $flags) {
            run(function () use ($flags) {
                $this->assertTrue(Coroutine::inCoroutine());
                $this->assertSame($flags, Runtime::getHookFlags());
            }, $flags);
        }
    }

    public function testDefer()
    {
        $channel = new Channel(10);
        parallel([function () use ($channel) {
            defer(function () use ($channel) {
                $channel->push(0);
            });
            defer(function () use ($channel) {
                $channel->push(1);
                defer(function () use ($channel) {
                    $channel->push(2);
                });
                defer(function () use ($channel) {
                    $channel->push(3);
                });
            });
            defer(function () use ($channel) {
                $channel->push(4);
            });
            $channel->push(5);
        }]);

        $this->assertSame(5, $channel->pop(0.001));
        $this->assertSame(4, $channel->pop(0.001));
        $this->assertSame(1, $channel->pop(0.001));
        $this->assertSame(3, $channel->pop(0.001));
        $this->assertSame(2, $channel->pop(0.001));
        $this->assertSame(0, $channel->pop(0.001));
    }
}
