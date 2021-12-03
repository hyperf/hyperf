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
namespace Hyperf\JsonRpc;

use Hyperf\JsonRpc\Pool\RpcConnection;
use Hyperf\Rpc\Exception\RecvException;
use Swoole\Coroutine\Client;

trait RecvTrait
{
    /**
     * @param Client|RpcConnection $client
     */
    public function recvAndCheck(mixed $client, float $timeout)
    {
        $data = $client->recv($timeout);
        if ($data === '') {
            // RpcConnection: When the next time the connection is taken out of the connection pool, it will reconnecting to the target service.
            // Client: It will reconnecting to the target service in the next request.
            $client->close();
            throw new RecvException('Connection is closed. ' . $client->errMsg, $client->errCode);
        }
        if ($data === false) {
            $client->close();
            throw new RecvException('Error receiving data, errno=' . $client->errCode . ' errmsg=' . swoole_strerror($client->errCode), $client->errCode);
        }

        return $data;
    }
}
