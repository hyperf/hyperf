<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
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
        $this->assertSame([2, 3, 4], $result);

        // Array
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add([$this, 'returnCoId']);
        }
        $result = $parallel->wait();
        $this->assertSame([5, 6, 7], $result);
    }

    public function returnCoId()
    {
        return Coroutine::id();
    }
}
