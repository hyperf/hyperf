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

namespace HyperfTest\LoadBalancer;

use Hyperf\LoadBalancer\Node;
use Hyperf\LoadBalancer\WeightedRandom;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class WeightedRandomTest extends TestCase
{
    public function testRandom()
    {
        $nodes = [
            new Node('127.0.0.1', 80, 10),
            new Node('127.0.0.2', 81, 20),
            new Node('127.0.0.3', 82, 10),
        ];
        $weightedRandom = new WeightedRandom($nodes);
        $node = $weightedRandom->select();
        $this->assertTrue(in_array($node, $nodes));
    }
}
