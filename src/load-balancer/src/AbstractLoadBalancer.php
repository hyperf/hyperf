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

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;

abstract class AbstractLoadBalancer implements LoadBalancerInterface
{
    /**
     * @param Node[] $nodes
     */
    public function __construct(protected array $nodes = [])
    {
    }

    /**
     * @param Node[] $nodes
     */
    public function setNodes(array $nodes): static
    {
        $this->nodes = $nodes;
        return $this;
    }

    public function getNodes(): array
    {
        return $this->nodes;
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

    public function refresh(callable $callback, int $tickMs = 5000): void
    {
        while (true) {
            try {
                $exited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield($tickMs / 1000);
                if ($exited) {
                    break;
                }

                $nodes = call($callback);
                is_array($nodes) && $this->setNodes($nodes);
            } catch (\Throwable) {
            }
        }
    }
}
