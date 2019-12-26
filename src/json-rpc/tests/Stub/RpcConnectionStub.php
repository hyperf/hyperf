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
        return true;
    }
}
