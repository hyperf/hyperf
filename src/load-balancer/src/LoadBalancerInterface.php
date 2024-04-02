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

use Closure;
use Hyperf\LoadBalancer\Exception\NoNodesAvailableException;

interface LoadBalancerInterface
{
    /**
     * Select an item via the load balancer.
     * @throws NoNodesAvailableException
     */
    public function select(array ...$parameters): Node;

    /**
     * @param Node[] $nodes
     */
    public function setNodes(array $nodes): static;

    /**
     * @return Node[] $nodes
     */
    public function getNodes(): array;

    /**
     * Remove a node from the node list.
     */
    public function removeNode(Node $node): bool;

    public function refresh(callable $callback, int $tickMs = 5000): void;

    public function isAutoRefresh(): bool;

    /**
     * Register a hook which will be executed after refresh nodes.
     */
    public function afterRefreshed(string $key, ?Closure $callback): void;

    /**
     * Clear all hooks which will be executed after refresh nodes.
     */
    public function clearAfterRefreshedCallbacks(): void;
}
