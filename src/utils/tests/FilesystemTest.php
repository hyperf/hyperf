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

use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;

/**
 * @internal
 * @coversNothing
 */
class FilesystemTest extends TestCase
{
    /**
     * @group NonCoroutine
     */
    public function testFopenInCoroutine()
    {
        run(function () {
            $max = 2;
            $chan = new Channel($max);
            go(function () use ($chan) {
                $handler = fopen(BASE_PATH . '/.travis/hyperf.sql', 'rb');
                $chan->push(1);
            });
            $chan->push(2);
            $result = [];

            for ($i = 0; $i < $max; ++$i) {
                $result[] = $chan->pop();
            }

            $this->assertSame([2, 1], $result);
        });
    }

    /**
     * @group NonCoroutine
     */
    public function testPutLockInCoroutine()
    {
        run(function () {
            $max = 3;
            $chan = new Channel($max);
            $path = BASE_PATH . '/runtime/data.log';
            go(function () use ($chan, $path) {
                $content = str_repeat('a', 70000);
                file_put_contents($path, $content, LOCK_EX);
                $chan->push(1);
            });
            go(function () use ($chan, $path) {
                $content = str_repeat('b', 70000);
                file_put_contents($path, $content, LOCK_EX);
                $chan->push(2);
            });
            $chan->push(3);
            $result = [];

            for ($i = 0; $i < $max; ++$i) {
                $result[] = $chan->pop();
            }

            $this->assertSame(3, $result[0]);
            $this->assertSame(70000, strlen(file_get_contents($path)));
            $content = file_get_contents($path);
            $this->assertTrue(
                str_repeat('a', 70000) == $content || str_repeat('b', 70000) == $content
            );
        });
    }
}
