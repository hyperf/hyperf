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
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
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
    protected $protocol = 'jsonrpc';

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
     * @var PathGeneratorInterface
     */
    protected $pathGenerator;

    /**
     * @var DataFormatterInterface
     */
    protected $dataFormatter;

    /**
     * @var \Hyperf\Contract\ConfigInterface
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->loadBalancerManager = $container->get(LoadBalancerManager::class);
        $this->protocolManager = $container->get(ProtocolManager::class);
        $this->pathGenerator = $this->createPathGenerator();
        $this->dataFormatter = $this->createDataFormatter();
        $this->client = $this->container->get(Client::class)
            ->setPacker($this->createPacker())
            ->setTransporter($this->createTransporter())
            ->setLoadBalancer($this->createLoadBalancer($this->createNodes()));
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
        return $this->pathGenerator->generate($this->serviceName, $methodName);
    }

    protected function __generateData(string $methodName, array $params)
    {
        return $this->dataFormatter->format([$this->serviceName, $this->__generateRpcPath($methodName), $params]);
    }

    protected function createLoadBalancer(array $nodes): LoadBalancerInterface
    {
        return $this->loadBalancerManager->getInstance($this->loadBalancer)->setNodes($nodes);
    }

    protected function createTransporter(?LoadBalancerInterface $loadBalancer = null): TransporterInterface
    {
        $transporter = $this->protocolManager->getTransporter($this->protocol);
        if (! class_exists($transporter)) {
            throw new InvalidArgumentException(sprintf('Transporter %s is not exists.', $transporter));
        }
        /** @var TransporterInterface $instance */
        $instance = $this->container->get($transporter);
        $loadBalancer && $instance->setLoadBalancer($loadBalancer);
        return $instance;
    }

    protected function createPacker(): PackerInterface
    {
        $packer = $this->protocolManager->getPacker($this->protocol);
        if (! class_exists($packer)) {
            throw new InvalidArgumentException(sprintf('Packer %s is not exists.', $packer));
        }
        /* @var PackerInterface $packer */
        return $this->container->get($packer);
    }

    protected function createPathGenerator(): PathGeneratorInterface
    {
        $pathGenerator = $this->protocolManager->getPathGenerator($this->protocol);
        if (! class_exists($pathGenerator)) {
            throw new InvalidArgumentException(sprintf('Path Generator %s is not exists.', $pathGenerator));
        }
        /* @var PathGeneratorInterface $pathGenerator */
        return $this->container->get($pathGenerator);
    }

    protected function createDataFormatter(): DataFormatterInterface
    {
        $dataFormatter = $this->protocolManager->getDataFormatter($this->protocol);
        if (! class_exists($dataFormatter)) {
            throw new InvalidArgumentException(sprintf('Data Formatter %s is not exists.', $dataFormatter));
        }
        /* @var DataFormatterInterface $dataFormatter */
        return $this->container->get($dataFormatter);
    }

    protected function createNodes(): array
    {
        if (! $this->container->has(ConfigInterface::class)) {
            throw new RuntimeException(sprintf('The object implementation of %s missing.', ConfigInterface::class));
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
        // Not exists the registry config, then looking for the 'nodes' property.
        if (isset($consumer['nodes'])) {
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
            throw new InvalidArgumentException('Component of \'hyperf/consul\' is required if you want the client fetch the nodes info from consul.');
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
                // @TODO Get and set the weight property.
                $nodes[] = new Node($service['Address'], $service['Port']);
            }
        }
        return $nodes;
    }
}
