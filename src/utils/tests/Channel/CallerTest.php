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
namespace HyperfTest\Utils\Channel;

use Hyperf\Coroutine\Exception\WaitTimeoutException;
use Hyperf\Utils\Channel\Caller;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class CallerTest extends TestCase
{
    public function testCallerWithNull()
    {
        $caller = new Caller(static function () {
            return null;
        });

        $id = $caller->call(static function ($instance) {
            return 1;
        });

        $this->assertSame(1, $id);

        $id = $caller->call(static function ($instance) {
            return 2;
        });

        $this->assertSame(2, $id);
    }

    public function testCaller()
    {
        $obj = new stdClass();
        $obj->id = uniqid();
        $caller = new Caller(static function () use ($obj) {
            return $obj;
        });

        $id = $caller->call(static function ($instance) {
            return $instance->id;
        });

        $this->assertSame($obj->id, $id);

        $caller->call(function ($instance) use ($obj) {
            $this->assertSame($instance, $obj);
        });
    }

    public function testCallerPopTimeout()
    {
        $obj = new stdClass();
        $obj->id = uniqid();
        $caller = new Caller(static function () use ($obj) {
            return $obj;
        }, 0.001);

        go(static function () use ($caller) {
            $caller->call(static function ($instance) {
                usleep(10 * 1000);
            });
        });

        $this->expectException(WaitTimeoutException::class);

        $caller->call(static function ($instance) {
            return 1;
        });
    }
}
