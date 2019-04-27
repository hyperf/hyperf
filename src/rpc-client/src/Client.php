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

        for ($i = 0; $i < 2; ++$i) {
            $result = $client->connect('0.0.0.0', 9502, $this->connectTimeout);
            if ($result === false && ($client->errCode == 114 or $client->errCode == 115)) {
                // Force close and reconnect to server.
                $client->close(true);
                continue;
            }

            break;
        }
        return $client;
    }

    public function send(array $data)
    {
        $connection = $this->getConnection();
        $sendData = json_encode($data);
        if ($connection->send($sendData . "\r\n") === false) {
            var_dump('send failure');
            if ($connection->errCode == 104) {
                // @TODO Reconnect to server.
            }
        }
        $response = $connection->recv($this->recvTimeout);
        return $this->packer->unpack($response);
    }
}
