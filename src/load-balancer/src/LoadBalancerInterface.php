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

namespace Hyperf\LoadBalancer;

interface LoadBalancerInterface
{
    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node;

    /**
     * @param \Hyperf\LoadBalancer\Node[] $nodes
     * @return $this
     */
    public function setNodes(array $nodes);

    /**
     * Remove a node from the node list.
     */
    public function removeNode(Node $node): bool;

    public function refresh(callable $callback, int $tickMs = 5000);
}
