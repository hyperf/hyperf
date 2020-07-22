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
use Hyperf\LoadBalancer\Random;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RandomTest extends TestCase
{
    public function testRandom()
    {
        $nodes = [
            new Node('127.0.0.1', 80),
            new Node('127.0.0.2', 81),
            new Node('127.0.0.3', 82),
        ];
        $random = new Random($nodes);
        $node = $random->select();
        $this->assertTrue(in_array($node, $nodes));
        $this->assertSame($nodes, $random->getNodes());
    }
}
