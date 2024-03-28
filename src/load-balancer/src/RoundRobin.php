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

namespace Hyperf\LoadBalancer;

use Hyperf\LoadBalancer\Exception\NoNodesAvailableException;

class RoundRobin extends AbstractLoadBalancer
{
    private static int $current = 0;

    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node
    {
        $count = count($this->nodes);
        if ($count <= 0) {
            throw new NoNodesAvailableException('Cannot select any node from load balancer.');
        }
        $item = $this->nodes[self::$current % $count];
        ++self::$current;
        return $item;
    }
}
