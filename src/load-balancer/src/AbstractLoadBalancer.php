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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class AbstractLoadBalancer implements LoadBalancerInterface
{
    /**
     * @var array<string, ?Closure>
     */
    protected array $afterRefreshCallbacks = [];

    protected bool $autoRefresh = false;

    /**
     * @param Node[] $nodes
     */
    public function __construct(protected array $nodes = [], protected ?LoggerInterface $logger = null)
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
        $this->autoRefresh = true;
        Coroutine::create(function () use ($callback, $tickMs) {
            while (true) {
                try {
                    $exited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield($tickMs / 1000);
                    if ($exited) {
                        break;
                    }

                    $beforeNodes = $this->getNodes();
                    $nodes = $callback();
                    if (is_array($nodes)) {
                        $this->setNodes($nodes);
                        foreach ($this->afterRefreshCallbacks as $refreshCallback) {
                            ! is_null($refreshCallback) && $refreshCallback($beforeNodes, $nodes);
                        }
                    }
                } catch (Throwable $exception) {
                    $this->logger?->error((string) $exception);
                }
            }
            $this->autoRefresh = false;
        });
    }

    public function isAutoRefresh(): bool
    {
        return $this->autoRefresh;
    }

    public function afterRefreshed(string $key, ?Closure $callback): void
    {
        $this->afterRefreshCallbacks[$key] = $callback;
    }

    public function clearAfterRefreshedCallbacks(): void
    {
        $this->afterRefreshCallbacks = [];
    }
}
