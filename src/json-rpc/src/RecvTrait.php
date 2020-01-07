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

namespace Hyperf\JsonRpc;

use Hyperf\JsonRpc\Pool\RpcConnection;
use Hyperf\Rpc\Exception\RecvException;
use Swoole\Coroutine\Client;

trait RecvTrait
{
    /**
     * @param Client|RpcConnection $client
     * @param float $timeout
     */
    public function recvAndCheck($client, $timeout)
    {
        $data = $client->recv((float) $timeout);
        if ($data === '') {
            throw new RecvException('Connection is closed.');
        }
        if ($data === false) {
            throw new RecvException('Error receiving data, errno=' . $client->errCode);
        }

        return $data;
    }
}
