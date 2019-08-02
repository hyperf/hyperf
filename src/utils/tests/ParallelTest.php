<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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
    public function testParallel1()
    {
        $id = Coroutine::id();
        // Closure
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add(function () {
                return Coroutine::id();
            });
        }
        $result = $parallel->wait();
        $this->assertSame(array_map(function ($i) use ($result) {
            return $i + $result[0];
        }, range(0, 2)), $result);

        // Array
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add([$this, 'returnCoId']);
        }
        $result = $parallel->wait();
        $this->assertSame(array_map(function ($i) use ($result) {
            return $i + $result[0];
        }, range(0, 2)), $result);
    }

    public function testParallel2()
    {
        $id = Coroutine::id();
        // Closure
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add(function () {
                return Coroutine::id();
            });
        }
        $result = $parallel->wait();
        $this->assertSame(array_map(function ($i) use ($result) {
            return $i + $result[0];
        }, range(0, 2)), $result);

        // Array
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add([$this, 'returnCoId']);
        }
        $result = $parallel->wait();
        $this->assertSame(array_map(function ($i) use ($result) {
            return $i + $result[0];
        }, range(0, 2)), $result);
    }

    public function returnCoId()
    {
        return Coroutine::id();
    }
}
