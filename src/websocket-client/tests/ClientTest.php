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

namespace HyperfTest\WebSocketClient;

use Hyperf\Codec\Json;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\WebSocketClient\Client;
use Hyperf\WebSocketClient\Exception\ConnectException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ClientTest extends TestCase
{
    public function testClientConnectFailed()
    {
        $this->expectException(ConnectException::class);

        new Client(new Uri('ws://172.168.1.1:9522'));
    }

    public function testClientConnected()
    {
        $client = new Client(new Uri('ws://127.0.0.1:10002/ws'));

        $client->push('ping');

        $this->assertSame('pong', $client->recv(1)->data);

        $client->close();
    }

    public function testClientHeaders()
    {
        $client = new Client(new Uri('ws://127.0.0.1:10002/ws'), ['x-token' => $token = uniqid()]);

        $client->push('headers');

        $data = $client->recv(1);
        $headers = Json::decode($data->data);

        $this->assertSame($token, $headers['x-token']);

        $client->close();
    }
}
