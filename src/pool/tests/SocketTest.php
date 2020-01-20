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

namespace HyperfTest\Pool;

use HyperfTest\Pool\Stub\SocketStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SocketTest extends TestCase
{
    public function testSocketConstruct()
    {
        $socket = new SocketStub($name = 'test', $host = '127.0.0.1', $port = 9511, $timeout = 10.0, $heartbeat = 5.0, false);

        $this->assertSame($name, $socket->getName());
        $this->assertSame($host, $socket->getHost());
        $this->assertSame($port, $socket->getPort());
        $this->assertSame($timeout, $socket->getTimeout());
        $this->assertSame($heartbeat, $socket->getHeartbeat());
    }
}
