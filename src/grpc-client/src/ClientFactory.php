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

namespace Hyperf\GrpcClient;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\GrpcClient\Exception\NoAvailableNodesException;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    protected ?LoadBalancerInterface $loadBalancer = null;

    /**
     * @var BaseClient[]
     */
    protected array $clients = [];

    public function __construct(protected ContainerInterface $container, protected array $config)
    {
    }

    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer)
    {
        if ($loadBalancer->isAutoRefresh()) {
            $this->bindAfterRefreshed($loadBalancer);
        }

        $this->loadBalancer = $loadBalancer;
    }

    public function refresh(): void
    {
        $nodes = $this->getNodes();
        $nodeCount = count($nodes);
        $count = $this->getCount();
        for ($i = 0; $i < $count; ++$i) {
            $node = $nodes[$i % $nodeCount];

            if (!isset($this->clients[$i]) || $this->clients[$i]->isConnected() === false) {
                $this->clients[$i] = make(BaseClient::class, [
                    'hostname' => $node->host . ':' . $node->port,
                    [
                        'connect_timeout' => $this->config['connect_timeout'],
                        'read_timeout' => $this->config['read_timeout'],
                    ]
                ]);
            }
        }
    }

    public function get(): BaseClient
    {
        if (count($this->clients) === 0) {
            $this->refresh();
        }
        $client = Arr::random($this->clients);
        if (!$client->isConnected()) {
            $this->refresh();
            $client = Arr::random($this->clients);
        }
        return $client;

    }

    protected function bindAfterRefreshed(LoadBalancerInterface $loadBalancer): void
    {
        $loadBalancer->afterRefreshed(static::class, function ($beforeNodes, $nodes) {
            $items = [];
            /** @var Node $node */
            foreach ($beforeNodes as $node) {
                $key = $node->host . $node->port . $node->weight . $node->pathPrefix;
                $items[$key] = true;
            }

            foreach ($nodes as $node) {
                $key = $node->host . $node->port . $node->weight . $node->pathPrefix;
                if (array_key_exists($key, $items)) {
                    unset($items[$key]);
                }
            }

            if (!empty($items)) {
                $this->refresh();
            }
        });
    }

    protected function getNodes(): array
    {
        $nodes = $this->getLoadBalancer()->getNodes();
        if (empty($nodes)) {
            throw new NoAvailableNodesException();
        }

        return $nodes;
    }

    protected function getCount(): int
    {
        return (int)($this->config['client_count']);
    }
}
