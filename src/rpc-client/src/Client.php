<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcClient;

use Hyperf\Contract\PackerInterface;
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
     * @var PackerInterface
     */
    private $packer;

    public function __construct(PackerInterface $packer)
    {
        $this->packer = $packer;
    }

    public function getConnection(): SwooleClient
    {
        $client = new SwooleClient(SWOOLE_SOCK_TCP);
        $client->set([
            'open_eof_split' => true,
            'package_eof' => "\r\n",
        ]);

        return retry(2, function () use ($client) {
            $result = $client->connect('0.0.0.0', 9502, $this->connectTimeout);
            if ($result === false && ($client->errCode == 114 or $client->errCode == 115)) {
                // Force close and reconnect to server.
                $client->close(true);
                throw new RuntimeException('Connect to server failure.');
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
                    throw new RuntimeException('Connect to server failure.');
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
