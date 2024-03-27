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

namespace HyperfTest\RpcClient;

use Hyperf\Codec\Json;
use Hyperf\Engine\Exception\SocketConnectException;
use Hyperf\Engine\Socket\SocketFactory;
use Hyperf\Engine\Socket\SocketOption;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ClientTest extends TestCase
{
    public function testConnectNotExistPort()
    {
        $this->expectException(SocketConnectException::class);

        (new SocketFactory())->make(new SocketOption('127.0.0.1', 10000));
    }

    public function testRecvTimeout()
    {
        $client = (new SocketFactory())->make(new SocketOption('127.0.0.1', 10001));

        $res = $client->send($data = Json::encode([
            'id' => 'timeout',
            'timeout' => 2,
        ]));
        $this->assertSame(strlen($data), $res);
        $data = $client->recvAll(1, 0.001);
        $this->assertFalse($data);
        $this->assertSame(SOCKET_ETIMEDOUT, $client->errCode);
    }

    public function testRecvData()
    {
        $client = (new SocketFactory())->make(new SocketOption('127.0.0.1', 10001));

        $res = $client->send($data = Json::encode([
            'id' => 'ack',
            'ack' => 2,
        ]));
        $this->assertSame(strlen($data), $res);
        $data = $client->recvAll(6, 1);
        $this->assertSame('ack: 2', $data);
        $this->assertSame(0, $client->errCode);
    }
}
