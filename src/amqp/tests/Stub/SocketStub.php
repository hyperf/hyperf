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
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Mockery;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Client;

class SocketStub extends Socket
{
    public function connect()
    {
        $sock = Mockery::mock(Client::class);
        $sock->connected = true;
        $sock->shouldReceive('send')->andReturnUsing(function ($data) {
            Context::set('test.amqp.send.data', $data);
        });
        $sock->shouldReceive('recv')->andReturnUsing(function ($timeout) {
            return Str::random(1);
        });

        $this->channel = new Channel(1);
        $this->channel->push($sock);
        $this->connected = true;

        $this->addHeartbeat();

        $this->heartbeat();
    }
}
