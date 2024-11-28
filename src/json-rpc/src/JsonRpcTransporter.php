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

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Engine\Contract\Socket\SocketFactoryInterface;
use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\Engine\Socket\SocketOption;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\TransporterInterface;
use RuntimeException;

use function Hyperf\Support\retry;

class JsonRpcTransporter implements TransporterInterface
{
    use RecvTrait;

    private ?LoadBalancerInterface $loadBalancer;

    /**
     * If $loadBalancer is null, will select a node in $nodes to request,
     * otherwise, use the nodes in $loadBalancer.
     *
     * @var Node[]
     */
    private array $nodes = [];

    private float $connectTimeout;

    private float $recvTimeout;

    private array $config = [];

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
            if ($client->sendAll($data) === false) {
                throw new RuntimeException('Connect to server failed.');
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

    public function getClient(): SocketInterface
    {
        $class = spl_object_hash($this) . '.Connection';
        if (Context::has($class)) {
            return Context::get($class);
        }

        return Context::set($class, retry(2, function () {
            $node = $this->getNode();
            return $this->getSocketFactory()->make(new SocketOption(
                $node->host,
                $node->port,
                $this->connectTimeout,
                $this->config['settings'] ?? []
            ));
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
     * @param Node[] $nodes
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

    private function getSocketFactory(): SocketFactoryInterface
    {
        return ApplicationContext::getContainer()->get(SocketFactoryInterface::class);
    }
}
