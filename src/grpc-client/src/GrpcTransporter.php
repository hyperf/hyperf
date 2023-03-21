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
namespace Hyperf\GrpcClient;

use Hyperf\Grpc\StatusCode;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\TransporterInterface;
use RuntimeException;

class GrpcTransporter implements TransporterInterface
{
    private ?LoadBalancerInterface $loadBalancer = null;

    /**
     * If $loadBalancer is null, will select a node in $nodes to request,
     * otherwise, use the nodes in $loadBalancer.
     *
     * @var Node[]
     */
    private array $nodes = [];

    public function send(string $data)
    {
        $node = $this->getNode();
        $unserializeData = unserialize($data);
        $method = $unserializeData['method'] ?? '';
        $id = $unserializeData['id'] ?? '';
        $params = $unserializeData['params'][0] ?? [];
        // TODO: Don't make new one when send messages.
        $client = new BaseClient($node->host . ':' . $node->port, []);
        $request = new Request($method, $params, []);
        $streamId = $client->send($request);
        $response = $client->recv($streamId);
        $client->close();
        if ($response->headers['grpc-status'] == StatusCode::OK) {
            $responseData = ['id' => $id, 'result' => $response->data];
        } else {
            $responseData = [
                'id' => $id,
                'error' => [
                    'code' => intval($response->headers['grpc-status']),
                    'message' => $response->headers['grpc-message'],
                ],
            ];
        }
        return serialize($responseData);
    }

    public function recv()
    {
        throw new RuntimeException(__CLASS__ . ' does not support recv method.');
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

    private function getNode(): Node
    {
        if ($this->loadBalancer instanceof LoadBalancerInterface) {
            return $this->loadBalancer->select();
        }
        return $this->nodes[array_rand($this->nodes)];
    }
}
