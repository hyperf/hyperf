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

use Hyperf\LoadBalancer\Node;
use Hyperf\LoadBalancer\Random;
use Hyperf\Rpc\Contract\PackerInterface;
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

    public function __construct(ContainerInterface $container)
    {
        $nodes = value(function () {
            // Retrieve the nodes from service center.
            return [new Node('127.0.0.1', 9502)];
        });
        $loadBalancer = new Random($nodes);
        $this->client = new Client($container->get(PackerInterface::class), new JsonRpcTransporter($loadBalancer));
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

    protected function __generateData(string $methodName, array $params)
    {
        return [
            'jsonrpc' => '2.0',
            'method' => $this->__generateRpcPath($methodName),
            'params' => $params,
        ];
    }
}
