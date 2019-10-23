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

namespace HyperfTest\Socket;

use Hyperf\Protocol\Packer\SerializePacker;
use Hyperf\Socket\Socket;
use HyperfTest\Socket\Stub\BigDemoStub;
use HyperfTest\Socket\Stub\DemoStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Socket as CoSocket;

/**
 * @internal
 * @coversNothing
 */
class SocketTest extends TestCase
{
    public function testSocketSend()
    {
        $cosocket = Mockery::mock(CoSocket::class);
        $cosocket->shouldReceive('sendAll')->with(Mockery::any(), Mockery::any())->andReturnUsing(function ($string, $timeout) {
            $this->assertEquals(-1, $timeout);
            return strlen($string);
        });
        $socket = new Socket($cosocket, new SerializePacker());
        $demo = new DemoStub();
        $res = $socket->send($demo);
        $this->assertSame(81, $res);
    }

    public function testSocketRecv()
    {
        $cosocket = Mockery::mock(CoSocket::class);
        $packer = new SerializePacker();
        $demo = new DemoStub();
        $cosocket->shouldReceive('recvAll')->once()->with(Mockery::any(), Mockery::any())->andReturnUsing(function ($length, $timeout) use ($demo) {
            $this->assertEquals(10, $timeout);
            if ($length == SerializePacker::HEAD_LENGTH) {
                return pack('N', strlen(serialize($demo)));
            }

            return serialize($demo);
        });
        $socket = new Socket($cosocket, $packer);
        $res = $socket->recv(10.0);
        $this->assertEquals($demo, $res);
    }

    public function testSocketRecvBigData()
    {
        $cosocket = Mockery::mock(CoSocket::class);
        $packer = new SerializePacker();
        $demo = new BigDemoStub();
        $data = array_merge([0], str_split(serialize($demo), Socket::RECV_MAX_LENGTH));
        $len = strlen(serialize($demo));
        $i = 0;

        $cosocket->shouldReceive('recvAll')->with(Mockery::any(), Mockery::any())->andReturnUsing(function ($length, $timeout) use ($len, $demo, $data, &$i) {
            if ($length == SerializePacker::HEAD_LENGTH) {
                return pack('N', $len);
            }
            if ($length < Socket::RECV_MAX_LENGTH) {
                $this->assertSame(strlen(serialize($demo)) % Socket::RECV_MAX_LENGTH, $length);
            }

            ++$i;

            return $data[$i];
        });

        $socket = new Socket($cosocket, $packer);
        $res = $socket->recv();

        $this->assertSame($i, intval($len / Socket::RECV_MAX_LENGTH) + 1);
        $this->assertEquals($demo, $res);
    }
}
