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

/**
 * @internal
 * @coversNothing
 */
class FilesystemTest extends TestCase
{
    public function testLock()
    {
        file_put_contents('./test.txt', str_repeat('a', 10000));
        $p = new Parallel();
        for ($i = 0; $i < 100; ++$i) {
            $p->add(function () {
                $fs = new \Hyperf\Utils\Filesystem\Filesystem();
                $fs->put('./test.txt', str_repeat('b', 1000000));
                $this->assertEquals(1000000, strlen($fs->get('./test.txt', true)));
            });
            $p->add(function () {
                $fs = new \Hyperf\Utils\Filesystem\Filesystem();
                $this->assertEquals(1000000, strlen($fs->get('./test.txt', true)));
                $fs->put('./test.txt', str_repeat('c', 1000000));
            });
        }
        $p->wait();
        unlink('./test.txt');
    }
}
