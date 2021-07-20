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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\RpcMultiplex\Exception\NoAvailableNodesException;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

class SocketFactory
{
    /**
     * @var null|LoadBalancerInterface
     */
    protected $loadBalancer;

    /**
     * @var Socket[]
     */
    protected $clients = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer)
    {
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
        return (int) $this->config['client_count'] ?? 4;
    }
}
