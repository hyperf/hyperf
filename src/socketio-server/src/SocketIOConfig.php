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

namespace Hyperf\SocketIOServer;

class SocketIOConfig
{
    private int $clientCallbackTimeout = 10000;

    private int $pingInterval = 10000;

    private int $pingTimeout = 100;

    public function getClientCallbackTimeout(): int
    {
        return $this->clientCallbackTimeout;
    }

    public function setClientCallbackTimeout(int $clientCallbackTimeout): void
    {
        $this->clientCallbackTimeout = $clientCallbackTimeout;
    }

    public function getPingInterval(): int
    {
        return $this->pingInterval;
    }

    public function setPingInterval(int $pingInterval): void
    {
        $this->pingInterval = $pingInterval;
    }

    public function getPingTimeout(): int
    {
        return $this->pingTimeout;
    }

    public function setPingTimeout(int $pingTimeout): void
    {
        $this->pingTimeout = $pingTimeout;
    }
}
