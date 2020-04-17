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

use Hyperf\Utils\Parallel;
use PHPUnit\Framework\TestCase;
use Swoole\Runtime;

/**
 * @internal
 * @coversNothing
 */
class FilesystemTest extends TestCase
{
    public function testLock()
    {
        Runtime::enableCoroutine(SWOOLE_HOOK_ALL);
        file_put_contents('./test.txt', str_repeat('a', 10000));
        $p = new Parallel();
        for ($i = 0; $i < 100; ++$i) {
            $p->add(function () {
                $fs = new \Hyperf\Utils\Filesystem\Filesystem();
                $fs->put('./test.txt', str_repeat('b', 100000), true);
                $this->assertEquals(100000, strlen($fs->get('./test.txt', true)));
            });
            $p->add(function () {
                $fs = new \Hyperf\Utils\Filesystem\Filesystem();
                $this->assertEquals(100000, strlen($fs->get('./test.txt', true)));
                $fs->put('./test.txt', str_repeat('c', 100000), true);
            });
        }
        $p->wait();
        unlink('./test.txt');
    }
}
