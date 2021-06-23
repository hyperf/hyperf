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

use Hyperf\Utils\Codec\Json;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Client;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    public function testConnectNotExistPort()
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $result = $client->connect('127.0.0.1', 10000);

        $this->assertFalse($result);
        $this->assertSame($client->errCode, SOCKET_ECONNREFUSED);
    }

    public function testRecvTimeout()
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $result = $client->connect('127.0.0.1', 10001);
        $this->assertTrue($result);

        $res = $client->send($data = Json::encode([
            'id' => 'timeout',
            'timeout' => 2,
        ]));
        $this->assertSame(strlen($data), $res);
        $data = $client->recv(0.001);
        $this->assertFalse($data);
        $this->assertSame(SOCKET_ETIMEDOUT, $client->errCode);
    }

    public function testRecvData()
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $result = $client->connect('127.0.0.1', 10001);
        $this->assertTrue($result);

        $res = $client->send($data = Json::encode([
            'id' => 'ack',
            'ack' => 2,
        ]));
        $this->assertSame(strlen($data), $res);
        $data = $client->recv(1);
        $this->assertSame('ack: 2', $data);
        $this->assertSame(0, $client->errCode);
    }
}
