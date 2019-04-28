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
use Hyperf\RpcClient\Pool\PoolFactory;
use RuntimeException;
use Swoole\Coroutine\Client as SwooleClient;

class Client
{
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
    private $config;

    /**
     * @var PackerInterface
     */
    private $packer;

    /**
     * @var PoolFactory
     */
    private $poolFactory;

    public function __construct(array $config, PackerInterface $packer, PoolFactory $poolFactory)
    {
        $this->config = $config;
        $this->packer = $packer;
        $this->poolFactory = $poolFactory;
    }

    public function getConnection(): SwooleClient
    {
        $client = new SwooleClient(SWOOLE_SOCK_TCP);
        $client->set($this->config['options'] ?? []);

        return retry(2, function () use ($client) {
            $result = $client->connect($this->config['host'], $this->config['port'], $this->connectTimeout);
            if ($result === false && ($client->errCode == 114 or $client->errCode == 115)) {
                // Force close and reconnect to server.
                $client->close(true);
                throw new RuntimeException('Connect to server failed.');
            }
            return $client;
        });
    }

    public function send(array $data)
    {
        $sendData = $this->packer->pack($data);
        $connection = retry(2, function () use ($sendData) {
            $connection = $this->getConnection();
            if ($connection->send($sendData . $this->getEof()) === false) {
                if ($connection->errCode == 104) {
                    throw new RuntimeException('Connect to server failed.');
                }
            }
            return $connection;
        });
        $response = $connection->recv($this->recvTimeout);
        return $this->packer->unpack($response);
    }

    private function getEof()
    {
        return "\r\n";
    }
}
