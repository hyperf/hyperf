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

namespace Hyperf\RpcClient;

use Hyperf\Consul\Health;
use Hyperf\Consul\HealthInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\LoadBalancerManager;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\Protocol;
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
    protected $protocol = 'jsonrpc-http';

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
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Hyperf\LoadBalancer\LoadBalancerManager
     */
    protected $loadBalancerManager;

    /**
     * @var null|\Hyperf\Contract\IdGeneratorInterface
     */
    protected $idGenerator;

    /**
     * @var PathGeneratorInterface
     */
    protected $pathGenerator;

    /**
     * @var DataFormatterInterface
     */
    protected $dataFormatter;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->loadBalancerManager = $container->get(LoadBalancerManager::class);
        $protocol = new Protocol($container, $container->get(ProtocolManager::class), $this->protocol);
        $loadBalancer = $this->createLoadBalancer(...$this->createNodes());
        $transporter = $protocol->getTransporter()->setLoadBalancer($loadBalancer);
        $this->client = make(Client::class)
            ->setPacker($protocol->getPacker())
            ->setTransporter($transporter);
        if ($container->has(IdGeneratorInterface::class)) {
            $this->idGenerator = $container->get(IdGeneratorInterface::class);
        }
        $this->pathGenerator = $protocol->getPathGenerator();
        $this->dataFormatter = $protocol->getDataFormatter();
    }

    protected function __request(string $method, array $params, ?string $id = null)
    {
        if ($this->idGenerator instanceof IdGeneratorInterface && ! $id) {
            $id = $this->idGenerator->generate();
        }
        $response = $this->client->send($this->__generateData($method, $params, $id));
        if (is_array($response)) {
            if (array_key_exists('result', $response)) {
                return $response['result'];
            }
            if (array_key_exists('error', $response)) {
                return $response['error'];
            }
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

    protected function __generateData(string $methodName, array $params, ?string $id)
    {
        return $this->dataFormatter->formatRequest([$this->__generateRpcPath($methodName), $params, $id]);
    }

    protected function createLoadBalancer(array $nodes, callable $refresh = null): LoadBalancerInterface
    {
        $loadBalancer = $this->loadBalancerManager->getInstance($this->serviceName, $this->loadBalancer)->setNodes($nodes);
        $refresh && $loadBalancer->refresh($refresh);
        return $loadBalancer;
    }

    /**
     * Create nodes the first time.
     *
     * @return array [array, callable]
     */
    protected function createNodes(): array
    {
        if (! $this->container->has(ConfigInterface::class)) {
            throw new RuntimeException(sprintf('The object implementation of %s missing.', ConfigInterface::class));
        }
        $refreshCallback = null;
        $config = $this->container->get(ConfigInterface::class);

        // According to the registry config of the consumer, retrieve the nodes.
        $consumers = $config->get('services.consumers', []);
        $isMatch = false;
        foreach ($consumers as $consumer) {
            if (isset($consumer['name']) && $consumer['name'] === $this->serviceName) {
                $isMatch = true;
                break;
            }
        }

        // Current $consumer is the config of the specified consumer.
        if ($isMatch && isset($consumer['registry']['protocol'], $consumer['registry']['address'])) {
            // According to the protocol and address of the registry, retrieve the nodes.
            switch ($registryProtocol = $consumer['registry']['protocol'] ?? '') {
                case 'consul':
                    $registry = $consumer['registry'] ?? [];
                    $nodes = $this->getNodesFromConsul($registry);
                    $refreshCallback = function () use ($registry) {
                        return $this->getNodesFromConsul($registry);
                    };
                    break;
                default:
                    throw new InvalidArgumentException(sprintf('Invalid protocol of registry %s', $registryProtocol));
                    break;
            }
            return [$nodes, $refreshCallback];
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
            return [$nodes, $refreshCallback];
        }
        throw new InvalidArgumentException('Config of registry or nodes missing.');
    }

    protected function getNodesFromConsul(array $config): array
    {
        $health = $this->createConsulHealth($config);
        $services = $health->service($this->serviceName)->json();
        $nodes = [];
        foreach ($services as $node) {
            $passing = true;
            $service = $node['Service'] ?? [];
            $checks = $node['Checks'] ?? [];

            foreach ($checks as $check) {
                $status = $check['Status'] ?? false;
                if ($status !== 'passing') {
                    $passing = false;
                }
            }

            if ($passing) {
                $address = $service['Address'] ?? '';
                $port = (int) $service['Port'] ?? 0;
                // @TODO Get and set the weight property.
                $address && $port && $nodes[] = new Node($address, $port);
            }
        }
        return $nodes;
    }

    protected function createConsulHealth(array $config): HealthInterface
    {
        if (! $this->container->has(Health::class)) {
            throw new InvalidArgumentException('Component of \'hyperf/consul\' is required if you want the client fetch the nodes info from consul.');
        }
        return make(Health::class, [
            'clientFactory' => function () use ($config) {
                return $this->container->get(ClientFactory::class)->create([
                    'base_uri' => $config['address'] ?? Health::DEFAULT_URI,
                ]);
            },
        ]);
    }
}
