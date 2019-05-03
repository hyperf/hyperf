<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcClient;

use Hyperf\Contract\PackerInterface;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\LoadBalancerManager;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\RpcServer\ProtocolManager;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;

abstract class AbstractServiceClient
{
    /**
     * @var string
     */
    protected $serviceName = '';

    /**
     * @var string
     */
    protected $protocol = 'jsonrpc-2.0';

    /**
     * @var string
     */
    protected $loadBalancer = 'random';

    /**
     * @var \Hyperf\RpcClient\Client
     */
    protected $client;

    /**
     * @var ContainerInterfaces
     */
    protected $container;

    /**
     * @var \Hyperf\LoadBalancer\LoadBalancerManager
     */
    protected $loadBalancerManager;

    /**
     * @var \Hyperf\RpcServer\ProtocolManager
     */
    protected $protocolManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->loadBalancerManager = $container->get(LoadBalancerManager::class);
        $this->protocolManager = $container->get(ProtocolManager::class);
        $loadBalancer = $this->createLoadBalancer($this->createNodes());
        $this->client = new Client($this->createPacker(), $this->createTransporter($loadBalancer));
    }

    protected function __request(string $method, array $params)
    {
        $response = $this->client->send($this->__generateData($method, $params));
        if (is_array($response) && isset($response['result'])) {
            return $response['result'];
        }
        throw new RuntimeException('Invalid response.');
    }

    protected function __generateRpcPath(string $methodName): string
    {
        if (! $this->serviceName) {
            throw new InvalidArgumentException('Parameter $serviceName missing.');
        }
        return '/' . $this->serviceName . '/' . $methodName;
    }

    protected function __generateData(string $methodName, array $params): array
    {
        return [
            'jsonrpc' => '2.0',
            'method' => $this->__generateRpcPath($methodName),
            'params' => $params,
        ];
    }

    protected function createLoadBalancer(array $nodes): LoadBalancerInterface
    {
        return $this->loadBalancerManager->getInstance($this->loadBalancer)->setNodes($nodes);
    }

    protected function createTransporter(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        $transporter = $this->protocolManager->getTransporter($this->protocol);
        if (! class_exists($transporter)) {
            throw new InvalidArgumentException(sprintf('Transporter %s not exists.', $transporter));
        }
        /** @var TransporterInterface $instance */
        $instance = $this->container->get($transporter);
        $instance->setLoadBalancer($loadBalancer);
        return $instance;
    }

    protected function createPacker(): PackerInterface
    {
        $packer = $this->protocolManager->getPacker($this->protocol);
        if (! class_exists($packer)) {
            throw new InvalidArgumentException(sprintf('Packer %s not exists.', $packer));
        }
        return $this->container->get($packer);
    }

    protected function createNodes(): array
    {
        return [new Node('127.0.0.1', 9502)];
    }
}
