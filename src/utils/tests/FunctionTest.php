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

use HyperfTest\Utils\Exception\RetryException;
use PHPUnit\Framework\TestCase;

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

        $obj = new \stdClass();
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

    /**
     * @expectedException \HyperfTest\Utils\Exception\RetryException
     */
    public function testRetry()
    {
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

    /**
     * @expectedException \HyperfTest\Utils\Exception\RetryException
     */
    public function testOneTimesRetry()
    {
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

    /**
     * @expectedException \HyperfTest\Utils\Exception\RetryException
     */
    public function testRetryErrorTimes()
    {
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

    public function testSwooleHookFlags()
    {
        $this->assertSame(SWOOLE_HOOK_ALL, swoole_hook_flags());
    }
}
