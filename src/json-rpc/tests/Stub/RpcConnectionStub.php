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

namespace HyperfTest\JsonRpc\Stub;

use Closure;
use Hyperf\JsonRpc\Pool\RpcConnection;

class RpcConnectionStub extends RpcConnection
{
    public $lastData = '';

    /**
     * @var null|Closure
     */
    public $reconnectCallback;

    public function __get($name)
    {
        return false;
        // return $this->connection->{$name};
    }

    public function send(string $data): false|int
    {
        $this->lastData = $data;
        return strlen($data);
    }

    public function recvPacket(float $timeout = 0): false|string
    {
        return $this->lastData;
    }

    public function reconnect(): bool
    {
        if ($this->reconnectCallback) {
            return $this->reconnectCallback->call($this);
        }
        $this->lastUseTime = microtime(true);
        return true;
    }

    public function close(): bool
    {
        $this->lastUseTime = 0.0;
        return true;
    }
}
