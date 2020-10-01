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
namespace Hyperf\JsonRpc;

use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\Utils\Context;
use RuntimeException;
use Swoole\Coroutine\Client as SwooleClient;

class JsonRpcTransporter implements TransporterInterface
{
    use RecvTrait;

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

    /**
     * @var array
     */
    private $config = [];

    public function __construct(array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);

        $this->recvTimeout = $this->config['recv_timeout'] ?? 5.0;
        $this->connectTimeout = $this->config['connect_timeout'] ?? 5.0;
    }

    public function send(string $data)
    {
        $client = retry(2, function () use ($data) {
            $client = $this->getClient();
            if ($client->send($data) === false) {
                if ($client->errCode == 104) {
                    throw new RuntimeException('Connect to server failed.');
                }
            }
            return $client;
        });

        return $this->recvAndCheck($client, $this->recvTimeout);
    }

    public function recv()
    {
        $client = $this->getClient();

        return $this->recvAndCheck($client, $this->recvTimeout);
    }

    public function getClient(): SwooleClient
    {
        $class = spl_object_hash($this) . '.Connection';
        if (Context::has($class)) {
            return Context::get($class);
        }

        return Context::set($class, retry(2, function () {
            $client = new SwooleClient(SWOOLE_SOCK_TCP);
            $client->set($this->config['settings'] ?? []);
            $node = $this->getNode();
            $result = $client->connect($node->host, $node->port, $this->connectTimeout);
            if ($result === false && ($client->errCode == 114 or $client->errCode == 115)) {
                // Force close and reconnect to server.
                $client->close();
                throw new RuntimeException('Connect to server failed.');
            }
            return $client;
        }));
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

    public function getNodes(): array
    {
        return $this->nodes;
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
