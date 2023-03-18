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

use Hyperf\Engine\Channel;
use Hyperf\Utils\Coroutine;
use HyperfTest\Utils\Exception\RetryException;
use HyperfTest\Utils\Stub\FooClosure;
use PHPUnit\Framework\TestCase;
use stdClass;
use Swoole\Runtime;

/**
 * @internal
 * @coversNothing
 */
class FunctionTest extends TestCase
{
    public function testCall()
    {
        $result = call(function ($i) {
            return ++$i;
        }, [1]);

        $this->assertSame(2, $result);
    }

    public function testReturnOfGo()
    {
        $uniqid = uniqid();
        $id = go(function () use (&$uniqid) {
            $uniqid = 'Hyperf';
        });

        $this->assertTrue(is_int($id));
        $this->assertSame('Hyperf', $uniqid);
    }

    public function testDataGet()
    {
        $data = ['id' => 1];
        $result = data_get($data, 'id');
        $this->assertSame(1, $result);
        $result = data_get($data, 'id2', 2);
        $this->assertSame(2, $result);

        $obj = new stdClass();
        $obj->name = 'hyperf';
        $data = ['id' => 2, 'obj' => $obj];
        $result = data_get($data, 'obj');
        $this->assertSame($obj, $result);
        $result = data_get($data, 'obj.name');
        $this->assertSame('hyperf', $result);
    }

    public function testDateGetIntegerKey()
    {
        $data = [1, 2, 3];
        $result = data_get($data, 0);
        $this->assertSame(1, $result);

        $data = ['a' => [1, 2, 3], 4];
        $result = data_get($data, 0);
        $this->assertSame(4, $result);
    }

    public function testRetry()
    {
        $this->expectException(\HyperfTest\Utils\Exception\RetryException::class);
        $result = 0;
        try {
            retry(2, function () use (&$result) {
                ++$result;
                throw new RetryException('Retry Test');
            });
        } finally {
            $this->assertSame(3, $result);
        }
    }

    public function testOneTimesRetry()
    {
        $this->expectException(\HyperfTest\Utils\Exception\RetryException::class);

        $result = 0;
        try {
            retry(1, function () use (&$result) {
                ++$result;
                throw new RetryException('Retry Test');
            });
        } finally {
            $this->assertSame(2, $result);
        }
    }

    public function testRetryErrorTimes()
    {
        $this->expectException(\HyperfTest\Utils\Exception\RetryException::class);

        $result = 0;
        try {
            retry(0, function () use (&$result) {
                ++$result;
                throw new RetryException('Retry Test');
            });
        } finally {
            $this->assertSame(1, $result);
        }
    }

    public function testRetryWithAttempts()
    {
        $this->expectException(\HyperfTest\Utils\Exception\RetryException::class);

        $asserts = [1, 2, 3];
        retry(2, function ($attempts) use (&$asserts) {
            $this->assertSame($attempts, array_shift($asserts));
            throw new RetryException('Retry Test');
        });
    }

    public function testSwooleHookFlags()
    {
        $this->assertSame(SWOOLE_HOOK_ALL, swoole_hook_flags());
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

    public function testFunctionValue()
    {
        $id = uniqid();
        $num = rand(1000, 9999);
        $assert = value(static function () use ($id) {
            return $id;
        });
        $this->assertSame($assert, $id);

        $assert = value($id);
        $this->assertSame($assert, $id);

        $assert = value(static function ($id, $num) {
            return $id . $num;
        }, $id, $num);
        $this->assertSame($assert, $id . $num);

        $assert = value($foo = new FooClosure(), $id);
        $this->assertSame($assert, $foo);
    }

    public function testEnv()
    {
        $id = 'NULL_' . uniqid();
        putenv("{$id}=(null)");

        $this->assertNull(env($id));
    }
}
