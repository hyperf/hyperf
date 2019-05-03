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

use Hyperf\Consul\Agent;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\LoadBalancerManager;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\Rpc\ProtocolManager;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;

abstract class AbstractServiceClient
{
    /**
     * The service name of the target service.
     *
     * @var string
     */
    protected $serviceName = '';

    /**
     * The protocol of the target service, this protocol name
     * needs to register into \Hyperf\Rpc\ProtocolManager.
     *
     * @var string
     */
    protected $protocol = 'jsonrpc-2.0';

    /**
     * The load balancer of the client, this name of the load balancer
     * needs to register into \Hyperf\LoadBalancer\LoadBalancerManager.
     *
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
     * @var \Hyperf\Rpc\ProtocolManager
     */
    protected $protocolManager;

    /**
     * @var \Hyperf\Contract\ConfigInterface
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->loadBalancerManager = $container->get(LoadBalancerManager::class);
        $this->protocolManager = $container->get(ProtocolManager::class);
        $loadBalancer = $this->createLoadBalancer($this->createNodes());
        $this->client = $this->container->get(Client::class)
            ->setPacker($this->createPacker())
            ->setTransporter($this->createTransporter($loadBalancer));
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
        /* @var PackerInterface $packer */
        return $this->container->get($packer);
    }

    protected function createNodes(): array
    {
        if (! $this->container->has(ConfigInterface::class)) {
            throw new RuntimeException(sprintf(
                'The object implementation of %s missing.',
                ConfigInterface::class
            ));
        }
        $config = $this->container->get(ConfigInterface::class);

        // According to the registry config of the consumer, retrieve the nodes.
        $consumers = $config->get('services.consumers');
        foreach ($consumers as $consumer) {
            if (isset($consumer['name']) && $consumer['name'] === $this->serviceName) {
                break;
            }
        }
        // Current $consumer is the config of the specified consumer.
        if (isset($consumer['registry']['protocol'], $consumer['registry']['address'])) {
            // According to the protocol and address of the registry, retrieve the nodes.
            switch ($registryProtocol = $consumer['registry']['protocol'] ?? '') {
                case 'consul':
                    $nodes = $this->getNodesFromConsul($consumer['registry'] ?? []);
                    break;
                default:
                    throw new InvalidArgumentException(sprintf('Invalid protocol of registry %s', $registryProtocol));
                    break;
            }
            return $nodes;
        }
        if (isset($consumer['nodes'])) {
            // Not exists the registry config, then looking for the 'nodes' property.
            $nodes = [];
            foreach ($consumer['nodes'] ?? [] as $item) {
                if (isset($item['host'], $item['port'])) {
                    if (! is_int($item['port'])) {
                        throw new InvalidArgumentException(sprintf('Invalid node config [%s], the port option has to a integer.', implode(':', $item)));
                    }
                    $nodes[] = new Node($item['host'], $item['port']);
                }
            }
            return $nodes;
        }
        throw new InvalidArgumentException('Config of registry or nodes missing.');
    }

    protected function getNodesFromConsul(array $config): array
    {
        if (! $this->container->has(Agent::class)) {
            throw new InvalidArgumentException(
                'Component of \'hyperf/consul\' is required if you want the client fetch the nodes info from consul.'
            );
        }
        $agent = make(Agent::class, [
            'clientFactory' => function () use ($config) {
                return $this->container->get(ClientFactory::class)->create([
                    'base_uri' => $config['address'] ?? null,
                ]);
            },
        ]);
        $services = $agent->services()->json();
        $nodes = [];
        foreach ($services as $serviceId => $service) {
            if (isset($service['Service'], $service['Address'], $service['Port']) && $service['Service'] === $this->serviceName) {
                $nodes[] = new Node($service['Address'], $service['Port']);
            }
        }
        return $nodes;
    }
}
