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

use HyperfTest\Pool\Stub\HeartbeatConnectionStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\Timer;

/**
 * @internal
 * @coversNothing
 */
class SocketTest extends TestCase
{
    protected function tearDown()
    {
        Timer::clearAll();
        Mockery::close();
    }

    public function testSocketConstruct()
    {
        $this->assertTrue(true);
        // $socket = new HeartbeatConnectionStub($name = 'test', $timeout = 10.0, $heartbeat = 5.0, [], false);
        //
        // $this->assertSame($name, $socket->getName());
        // $this->assertSame($timeout, $socket->getTimeout());
        // $this->assertSame($heartbeat, $socket->getHeartbeat());
    }

    // public function testSocketDestruct()
    // {
    //     $socket = new HeartbeatConnectionStub($name = 'test', $timeout = 10.0, $heartbeat = 5.0, [], false);
    // }
}
