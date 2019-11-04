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

use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Parallel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Hyperf\Utils\Parallel
 */
class ParallelTest extends TestCase
{
    public function testParallel()
    {
        // Closure
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add(function () {
                return Coroutine::id();
            });
        }
        $result = $parallel->wait();
        $id = $result[0];
        $this->assertSame([$id, $id + 1, $id + 2], $result);

        // Array
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add([$this, 'returnCoId']);
        }
        $result = $parallel->wait();
        $id = $result[0];
        $this->assertSame([$id, $id + 1, $id + 2], $result);
    }

    public function testParallelConcurrent()
    {
        $parallel = new Parallel();
        $num = 0;
        $callback = function () use (&$num) {
            ++$num;
            Coroutine::sleep(0.01);
            return $num;
        };
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $this->assertSame([4, 4, 4, 4], array_values($res));

        $parallel = new Parallel(2);
        $num = 0;
        $callback = function () use (&$num) {
            ++$num;
            Coroutine::sleep(0.01);
            return $num;
        };
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        sort($res);
        $this->assertSame([2, 3, 4, 4], array_values($res));
    }
    
    public function testParallelConcurrentResultCount()
    {
        $parallel = new Parallel();
        $num = 0;
        $callback = function () {
            return 1;
        };
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $this->assertEquals(count($res), 4);
        
        
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $this->assertEquals(count($res), 4);
    }

    public function returnCoId()
    {
        return Coroutine::id();
    }
}
