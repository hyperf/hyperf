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

use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\LoadBalancer\Random;
use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\RpcClient\Transporter\JsonRpcTransporter;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;

abstract class AbstractServiceClient
{
    /**
     * @var string
     */
    public $serviceName = '';

    /**
     * @var \Hyperf\RpcClient\Client
     */
    protected $client;

    /**
     * @var ContainerInterfaces
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $nodes = $this->createNodes();
        $loadBalancer = $this->createLoadBalancer($nodes);
        $this->client = new Client($this->createPacker(), $this->createTransporter($loadBalancer));
    }

    protected function __request(string $methodName, array $params)
    {
        $response = $this->client->send($this->__generateData($methodName, $params));
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
        return new Random($nodes);
    }

    protected function createTransporter(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        return new JsonRpcTransporter($loadBalancer);
    }

    protected function createPacker(): PackerInterface
    {
        return $this->container->get(PackerInterface::class);
    }

    protected function createNodes(): array
    {
        return [new Node('127.0.0.1', 9502)];
    }
}
