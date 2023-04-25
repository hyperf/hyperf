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
namespace HyperfTest\Support;

use HyperfTest\Utils\Exception\RetryException;
use HyperfTest\Utils\Stub\FooClosure;
use PHPUnit\Framework\TestCase;

use function Hyperf\Support\call;
use function Hyperf\Support\env;
use function Hyperf\Support\retry;
use function Hyperf\Support\swoole_hook_flags;
use function Hyperf\Support\value;

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
