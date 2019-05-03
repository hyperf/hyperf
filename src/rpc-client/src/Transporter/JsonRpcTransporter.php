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

namespace Hyperf\RpcClient\Transporter;

use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\TransporterInterface;
use RuntimeException;
use Swoole\Coroutine\Client as SwooleClient;

class JsonRpcTransporter implements TransporterInterface
{
    /**
     * @var LoadBalancerInterface
     */
    private $loadBalancer;

    /**
     * @var float
     */
    private $connectTimeout = 5;

    /**
     * @var float
     */
    private $recvTimeout = 5;

    public function __construct(LoadBalancerInterface $loadBalancer)
    {
        $this->loadBalancer = $loadBalancer;
    }

    public function send(string $data)
    {
        $client = retry(2, function () use ($data) {
            $client = $this->getClient();
            if ($client->send($data . $this->getEof()) === false) {
                if ($client->errCode == 104) {
                    throw new RuntimeException('Connect to server failed.');
                }
            }
            return $client;
        });
        return $client->recv($this->recvTimeout);
    }

    public function getClient(): SwooleClient
    {
        $client = new SwooleClient(SWOOLE_SOCK_TCP);
        $client->set($this->config['options'] ?? []);

        return retry(2, function () use ($client) {
            $node = $this->getNode();
            $result = $client->connect($node->host, $node->port, $this->connectTimeout);
            if ($result === false && ($client->errCode == 114 or $client->errCode == 115)) {
                // Force close and reconnect to server.
                $client->close(true);
                throw new RuntimeException('Connect to server failed.');
            }
            return $client;
        });
    }

    private function getEof()
    {
        return "\r\n";
    }

    private function getNode(): Node
    {
        /** @var \Hyperf\LoadBalancer\Node $node */
        $node = $this->loadBalancer->select();
        return $node;
    }
}
