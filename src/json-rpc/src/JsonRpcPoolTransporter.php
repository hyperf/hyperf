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

use Hyperf\Contract\ConnectionInterface;
use Hyperf\JsonRpc\Pool\PoolFactory;
use Hyperf\JsonRpc\Pool\RpcConnection;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Pool\Pool;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\Utils\Context;
use RuntimeException;

class JsonRpcPoolTransporter implements TransporterInterface
{
    use RecvTrait;

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

    private $config = [
        'connect_timeout' => 5.0,
        'settings' => [],
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

    public function __construct(PoolFactory $factory, array $config = [])
    {
        $this->factory = $factory;
        $this->config = array_replace_recursive($this->config, $config);

        $this->recvTimeout = $this->config['recv_timeout'] ?? 5.0;
        $this->connectTimeout = $this->config['connect_timeout'] ?? 5.0;
    }

    public function send(string $data)
    {
        $client = retry(2, function () use ($data) {
            try {
                $client = $this->getConnection();
                if ($client->send($data) === false) {
                    if ($client->errCode == 104) {
                        throw new RuntimeException('Connect to server failed.');
                    }
                }
                return $client;
            } catch (\Throwable $throwable) {
                if (isset($client) && $client instanceof ConnectionInterface) {
                    $client->close();
                }
                throw $throwable;
            }
        });

        return $this->recvAndCheck($client, $this->recvTimeout);
    }

    public function recv()
    {
        $client = $this->getConnection();

        return $this->recvAndCheck($client, $this->recvTimeout);
    }

    /**
     * Get RpcConnection from Context.
     */
    public function getConnection(): RpcConnection
    {
        $class = spl_object_hash($this) . '.Connection';
        if (Context::has($class)) {
            return Context::get($class);
        }

        $connection = $this->getPool()->get();

        defer(function () use ($connection) {
            $connection->release();
        });

        return Context::set($class, $connection->getConnection());
    }

    public function getPool(): Pool
    {
        $name = spl_object_hash($this) . '.Pool';
        $config = [
            'connect_timeout' => $this->config['connect_timeout'],
            'settings' => $this->config['settings'],
            'node' => function () {
                return $this->getNode();
            },
        ];

        return $this->factory->getPool($name, $config);
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
