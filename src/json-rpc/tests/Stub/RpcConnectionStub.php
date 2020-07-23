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

use Hyperf\JsonRpc\Pool\RpcConnection;

class RpcConnectionStub extends RpcConnection
{
    public $lastData = '';

    public function __call($name, $arguments)
    {
        if ($name == 'send') {
            $this->lastData = $arguments[0];
            return strlen($arguments[0]);
        }

        if ($name == 'recv') {
            return $this->lastData;
        }

        return false;
    }

    public function __get($name)
    {
        return false;
        // return $this->connection->{$name};
    }

    public function reconnect(): bool
    {
        $this->lastUseTime = microtime(true);
        return true;
    }

    public function close(): bool
    {
        $this->lastUseTime = 0.0;
        return true;
    }
}
