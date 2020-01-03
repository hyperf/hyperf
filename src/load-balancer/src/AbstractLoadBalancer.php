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

use Swoole\Timer;

abstract class AbstractLoadBalancer implements LoadBalancerInterface
{
    /**
     * @var Node[]
     */
    protected $nodes;

    /**
     * @param \Hyperf\LoadBalancer\Node[] $nodes
     */
    public function __construct(array $nodes = [])
    {
        $this->nodes = $nodes;
    }

    /**
     * @param \Hyperf\LoadBalancer\Node[] $nodes
     * @return $this
     */
    public function setNodes(array $nodes)
    {
        $this->nodes = $nodes;
        return $this;
    }

    /**
     * Remove a node from the node list.
     */
    public function removeNode(Node $node): bool
    {
        foreach ($this->nodes as $key => $activeNode) {
            if ($activeNode === $node) {
                unset($this->nodes[$key]);
                return true;
            }
        }
        return false;
    }

    public function refresh(callable $callback, int $tickMs = 5000)
    {
        Timer::tick($tickMs, function () use ($callback) {
            $nodes = call($callback);
            is_array($nodes) && $this->setNodes($nodes);
        });
    }
}
