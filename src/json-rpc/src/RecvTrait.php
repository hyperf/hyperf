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

use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\JsonRpc\Pool\RpcConnection;
use Hyperf\Rpc\Exception\RecvException;

trait RecvTrait
{
    /**
     * @param RpcConnection|SocketInterface $client
     */
    public function recvAndCheck(mixed $client, float $timeout)
    {
        $data = $client->recvPacket($timeout);
        if ($data === '') {
            // RpcConnection: When the next time the connection is taken out of the connection pool, it will reconnect to the target service.
            // Client: It will reconnect to the target service in the next request.
            $client->close();
            throw new RecvException('Connection is closed. ' . $client->errMsg, $client->errCode);
        }
        if ($data === false) {
            $client->close();
            throw new RecvException('Error receiving data, errno=' . $client->errCode . ' errmsg=' . $client->errMsg, $client->errCode);
        }

        return $data;
    }
}
