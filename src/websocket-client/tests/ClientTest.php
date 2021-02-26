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

use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\WebSocketClient\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    public function testClientConnectFailed()
    {
        $this->expectException(\Hyperf\WebSocketClient\Exception\ConnectException::class);

        new Client(new Uri('ws://172.168.1.1:9522'));
    }
}
