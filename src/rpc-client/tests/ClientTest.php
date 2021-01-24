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
}
