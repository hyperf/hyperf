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

namespace HyperfTest\Stdlib;

use Hyperf\Stdlib\SplPriorityQueue;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SplPriorityQueueTest extends TestCase
{
    public function testQueueWithPriority()
    {
        $items = [
            'a' => 1,
            'b' => 2,
            'c' => 0,
            'd' => 1,
        ];
        $queue = new SplPriorityQueue();
        foreach ($items as $value => $priority) {
            $queue->insert($value, $priority);
        }
        $result = [];
        foreach ($queue as $value) {
            $result[] = $value;
        }
        $this->assertSame('b,a,d,c', join(',', $result));
    }

    public function testQueueWithSomePriority()
    {
        $items = ['a', 'b', 'c', 'd' => 1];
        $queue = new SplPriorityQueue();
        foreach ($items as $value => $priority) {
            if (! is_int($priority)) {
                [$priority, $value] = [0, $priority];
            }
            $queue->insert($value, $priority);
        }
        $result = [];
        foreach ($queue as $value) {
            $result[] = $value;
        }
        $this->assertSame('d,a,b,c', join(',', $result));
    }

    public function testQueueWithoutPriority()
    {
        $items = ['a', 'b', 'c', 'd'];
        $queue = new SplPriorityQueue();
        foreach ($items as $value => $priority) {
            if (! is_int($priority)) {
                [$priority, $value] = [0, $priority];
            }
            $queue->insert($value, $priority);
        }
        $result = [];
        foreach ($queue as $value) {
            $result[] = $value;
        }
        $this->assertSame('a,b,c,d', join(',', $result));
    }

    public function testQueueWithArrayPriority()
    {
        $items = [
            'a' => [1, 2],
            'b' => [2, 1],
            'c' => [0, 3],
            'd' => [1, 2],
        ];
        $queue = new SplPriorityQueue();
        foreach ($items as $value => $priority) {
            $queue->insert($value, $priority);
        }
        $result = [];
        foreach ($queue as $value) {
            $result[] = $value;
        }
        $this->assertSame('b,a,d,c', join(',', $result));
    }
}
