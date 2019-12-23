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

namespace Hyperf\JsonRpc;

use Hyperf\Contract\ConfigInterface;
use Hyperf\JsonRpc\Pool\PoolFactory;
use Hyperf\JsonRpc\Pool\RpcConnection;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Pool\Pool;
use Hyperf\Rpc\Contract\TransporterInterface;
use RuntimeException;

class JsonRpcPoolTransporter extends AbstractJsonRpcTransporter implements TransporterInterface
{
    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * @var null|LoadBalancerInterface
     */
    private $loadBalancer;

    /**
     * If $loadBalancer is null, will select a node in $nodes to request,
     * otherwise, use the nodes in $loadBalancer.
     *
     * @var Node[]
     */
    private $nodes = [];

    /**
     * @var float
     */
    private $connectTimeout = 5;

    /**
     * @var float
     */
    private $recvTimeout = 5;

    public function __construct(PoolFactory $factory, ConfigInterface $conf, array $config = [])
    {
        parent::__construct($conf);

        $this->factory = $factory;
        $this->config = array_replace_recursive($this->config, $this->getDefaultConfig(), $config);
    }

    public function send(string $data)
    {
        $client = retry(2, function () use ($data) {
            $pool = $this->getPool();
            $connection = $pool->get();
            try {
                /** @var RpcConnection $client */
                $client = $connection->getConnection();
                if ($client->send($data) === false) {
                    if ($client->errCode == 104) {
                        throw new RuntimeException('Connect to server failed.');
                    }
                }
                return $client;
            } catch (\Throwable $throwable) {
                if ($connection instanceof RpcConnection) {
                    // Reconnect again next time.
                    $connection->resetLastUseTime();
                }
                $connection->release();
                throw $throwable;
            }
        });
        try {
            $data = $client->recv($this->recvTimeout);
        } finally {
            $client->release();
        }
        return $data;
    }

    public function getPool(): Pool
    {
        $node = $this->getNode();
        $name = sprintf('%s:%s', $node->host, $node->port);
        return $this->factory->getPool($name, [
            'host' => $node->host,
            'port' => $node->port,
            'connect_timeout' => $this->getConfig()['connect_timeout'],
            'options' => $this->getConfig()['options'],
        ]);
    }

    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        $this->loadBalancer = $loadBalancer;
        return $this;
    }

    /**
     * @param \Hyperf\LoadBalancer\Node[] $nodes
     */
    public function setNodes(array $nodes): self
    {
        $this->nodes = $nodes;
        return $this;
    }

    /**
     * @return \Hyperf\LoadBalancer\Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function getDefaultConfig()
    {
        return [
            'connect_timeout' => 5.0,
            'eof' => "\r\n",
            'options' => [],
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 32,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'max_idle_time' => 60.0,
            ],
            'recv_timeout' => 5.0,
        ];
    }

    /**
     * If the load balancer is exists, then the node will select by the load balancer,
     * otherwise will get a random node.
     */
    private function getNode(): Node
    {
        if ($this->loadBalancer instanceof LoadBalancerInterface) {
            return $this->loadBalancer->select();
        }
        return $this->nodes[array_rand($this->nodes)];
    }
}
