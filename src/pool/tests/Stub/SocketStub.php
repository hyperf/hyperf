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

namespace HyperfTest\Pool\Stub;

use Hyperf\Pool\Socket;
use Swoole\Coroutine;

class SocketStub extends Socket
{
    public function getChannel(): Coroutine\Channel
    {
        return $this->channel;
    }

    public function getLastUseTime(): float
    {
        return $this->lastUseTime;
    }

    public function getTimerId(): ?int
    {
        return $this->timerId;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getHeartbeat(): float
    {
        return $this->heartbeat;
    }

    public function heartbeat(): void
    {
        // TODO: Implement heartbeat() method.
    }

    protected function connect()
    {
    }

    protected function sendClose($socket): void
    {
    }
}
