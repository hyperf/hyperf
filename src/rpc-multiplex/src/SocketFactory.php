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
namespace Hyperf\RpcMultiplex;

use Hyperf\Collection\Arr;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\RpcMultiplex\Exception\NoAvailableNodesException;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class SocketFactory
{
    protected ?LoadBalancerInterface $loadBalancer = null;

    /**
     * @var Socket[]
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
            if (! isset($this->clients[$i])) {
                $this->clients[$i] = make(Socket::class);
            }
            $client = $this->clients[$i];
            $node = $nodes[$i % $nodeCount];
            $client->setName($node->host)->setPort($node->port)->set([
                'package_max_length' => $this->config['settings']['package_max_length'] ?? 1024 * 1024 * 2,
                'recv_timeout' => $this->config['recv_timeout'] ?? 10,
                'connect_timeout' => $this->config['connect_timeout'] ?? 0.5,
                'heartbeat' => $this->config['heartbeat'] ?? null,
            ]);
            if ($this->container->has(StdoutLoggerInterface::class)) {
                $client->setLogger($this->container->get(StdoutLoggerInterface::class));
            }
        }
    }

    public function get(): Socket
    {
        if (count($this->clients) === 0) {
            $this->refresh();
        }

        return Arr::random($this->clients);
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

            if (! empty($items)) {
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
        return (int) ($this->config['client_count'] ?? 4);
    }
}
