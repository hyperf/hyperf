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

namespace HyperfTest\Support\Filesystem;

use Hyperf\Coroutine\Parallel;
use Hyperf\Engine\Channel;
use Hyperf\Support\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Swoole\Runtime;
use Throwable;

use function Hyperf\Coroutine\go;
use function Hyperf\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FilesystemTest extends TestCase
{
    public function testLock()
    {
        Runtime::enableCoroutine(SWOOLE_HOOK_ALL);
        file_put_contents('./test.txt', str_repeat('a', 10000));
        $p = new Parallel();
        for ($i = 0; $i < 100; ++$i) {
            $p->add(function () {
                $fs = new Filesystem();
                $fs->put('./test.txt', str_repeat('b', 100000), true);
                $this->assertEquals(100000, strlen($fs->get('./test.txt', true)));
            });
            $p->add(function () {
                $fs = new Filesystem();
                $this->assertEquals(100000, strlen($fs->get('./test.txt', true)));
                $fs->put('./test.txt', str_repeat('c', 100000), true);
            });
        }
        $p->wait();
        unlink('./test.txt');
    }

    #[Group('NonCoroutine')]
    public function testFopenInCoroutine()
    {
        if (SWOOLE_VERSION_ID >= 60000) {
            $this->markTestSkipped();
        }

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

    public function testMakeDirection()
    {
        $system = new Filesystem();
        try {
            $system->makeDirectory(BASE_PATH . '/runtime/test');
            $system->makeDirectory(BASE_PATH . '/runtime/test');
        } catch (Throwable $exception) {
            $this->assertSame('mkdir(): File exists', $exception->getMessage());
        }

        $this->assertTrue(true);
    }

    #[Group('NonCoroutine')]
    public function testPutLockInCoroutine()
    {
        if (SWOOLE_VERSION_ID >= 60000) {
            $this->markTestSkipped();
        }

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

    public function testLastModified()
    {
        $path = BASE_PATH . '/runtime/data.log';
        $fs = new Filesystem();

        $fs->makeDirectory(BASE_PATH . '/runtime');
        $this->assertNotFalse($fs->put($path, 'hello'));
        $lastModified = $fs->lastModified($path);

        sleep(1);

        $this->assertNotFalse($fs->put($path, 'world'));
        $this->assertSame($lastModified, $fs->lastModified($path));

        $fs->clearStatCache($path);
        $this->assertNotSame($lastModified, $fs->lastModified($path));

        unlink($path);
    }
}
