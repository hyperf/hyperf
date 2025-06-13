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
use Hyperf\LoadBalancer\WeightedRoundRobin;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class WeightedRoundRobinTest extends TestCase
{
    public function testRandom()
    {
        $nodes = [
            new Node($ip1 = '127.0.0.1', 80, 10),
            new Node($ip2 = '127.0.0.2', 81, 20),
            new Node($ip3 = '127.0.0.3', 82, 10),
        ];
        $weightedRoundRobin = new WeightedRoundRobin($nodes);
        $ips = [];
        for ($i = 0; $i < 4; ++$i) {
            $node = $weightedRoundRobin->select();
            if (! isset($ips[$node->host])) {
                $ips[$node->host] = 1;
            } else {
                ++$ips[$node->host];
            }
        }
        $this->assertSame(1, $ips[$ip1]);
        $this->assertSame(2, $ips[$ip2]);
        $this->assertSame(1, $ips[$ip3]);
    }
}
