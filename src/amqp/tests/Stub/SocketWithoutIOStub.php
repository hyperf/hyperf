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

namespace HyperfTest\Amqp\Stub;

use Hyperf\Amqp\Connection\Socket;
use Swoole\Coroutine\Channel;

class SocketWithoutIOStub extends Socket
{
    public $connectCount = 0;

    public function __construct(bool $connected, string $host, int $port, float $timeout, int $heartbeat)
    {
        parent::__construct($host, $port, $timeout, $heartbeat);
        $this->connected = $connected;
    }

    public function connect()
    {
        $this->channel = new Channel(1);
        $this->waitTimeout = 0.1;
        ++$this->connectCount;

        $this->addHeartbeat();
    }

    public function getConnectCount(): int
    {
        return $this->connectCount;
    }
}
