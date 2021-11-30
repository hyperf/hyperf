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
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
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

    /**
     * @var null|LoggerInterface
     */
    protected $logger;

    protected $logInterval = 3;
    protected $id = '';
    protected $logHasEnabled = false;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
        $this->id = uniqid();
        $this->logClients();

        if ($this->container->has(StdoutLoggerInterface::class)) {
            $this->logger = $this->container->get(StdoutLoggerInterface::class);
        }
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
            ])->keepHealthy([$this, 'resetClient'], $i);
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

        // only get healthy client
        $okClients = array_filter($this->clients, function($item) {
            return $item->isHealthy;
        });
        return Arr::random($okClients);
    }

    /**
     * auto reset client node
     */
    public function resetClient($index) {
        if (!isset($this->clients[$index])) {
            return;
        }
        $node = $this->getLoadBalancer()->select();
        if (empty($node)) {
            return;
        }
        $this->logger && $this->logger->debug(sprintf('resetClient node %s:%d', $node->host, $node->port));
        $client = $this->clients[$index];
        $client->setName($node->host)->setPort($node->port);
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

    /**
     * log clients
     */
    protected function logClients()
    {
        if ($this->logHasEnabled) {
            return;
        }
        $this->logHasEnabled = true;
        $heartbeat = $this->logInterval;
        Coroutine::create(function () use ($heartbeat) {
            while (true) {
                if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($heartbeat)) {
                    break;
                }

                $clientArr = [];
                foreach ($this->clients as $client) {
                    $clientArr[] = $client->getInfo();
                }

                $this->logger && $this->logger->debug(sprintf('SocketFactory %s clients %s', $this->id, json_encode($clientArr)));
            }
        });
    }
}
