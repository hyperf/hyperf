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

use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Swoole\Timer;

abstract class AbstractLoadBalancer implements LoadBalancerInterface
{
    /**
     * @var Node[]
     */
    protected $nodes;

    /**
     * @var null|string
     */
    protected $registryProtocol = null;

    /**
     * @param \Hyperf\LoadBalancer\Node[] $nodes
     */
    public function __construct(array $nodes = [])
    {
        $this->nodes = $nodes;
    }

    public function getNodeCount(): int
    {
        return count($this->nodes);
    }

    public function getNodes(): array
    {
        return $this->nodes;
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
     * @return string|null
     */

    public function getRegistryProtocol()
    {
        return $this->registryProtocol;
    }

    /**
     * @param string|null $registryProtocol
     * @return $this
     */
    public function setRegistryProtocol(string $registryProtocol = null)
    {
        $this->registryProtocol = $registryProtocol;
        return $this;
    }

    /**
     * Remove a node from the node list.
     * @return bool
     */
    public function removeNode(Node $node):bool
    {
        foreach ($this->nodes as $key => $activeNode) {
            if ((string)$activeNode === (string)$node) {
                unset($this->nodes[$key]);
                if (!$this->registryProtocol) {
                    $this->reJoinLocalNode($node);
                }
                return true;
            }
        }
        return false;
    }

    protected function reJoinLocalNode(Node $node)
    {
        Coroutine::create(function () use ($node) {
            sleep(10);
            $this->nodes[] = $node;
        });
    }

    public function refresh(callable $callback, int $tickMs = 5000)
    {
        $timerId = Timer::tick($tickMs, function () use ($callback) {
            $nodes = call($callback);
            is_array($nodes) && $this->setNodes($nodes);
        });
        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            Timer::clear($timerId);
        });
    }
}
