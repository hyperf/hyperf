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

namespace HyperfTest\Socket;

use Hyperf\Protocol\Packer\SerializePacker;
use Hyperf\Socket\Socket;
use HyperfTest\Socket\Stub\DemoStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Socket as CoSocket;
use Swoole\Process;

use function Hyperf\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class SocketTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

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
        $cosocket->shouldReceive('recvAll')->twice()->with(Mockery::any(), Mockery::any())->andReturnUsing(function ($length, $timeout) use ($demo) {
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

    #[Group('NonCoroutine')]
    public function testProcessStreamSocket()
    {
        $demo = new DemoStub();
        $process = new Process(function (Process $process) use ($demo) {
            $socket = new Socket($process->exportSocket(), new SerializePacker());
            $ret = $socket->recv();
            $this->assertSame($demo->unique, $ret->unique);
            $socket->send('end');
        }, false, SOCK_STREAM, true);

        $process->start();

        run(function () use ($process, $demo) {
            $socket = new Socket($process->exportSocket(), new SerializePacker());
            $ret = $socket->send($demo);
            $this->assertSame(81, $ret);
            $ret = $socket->recv();
            $this->assertSame('end', $ret);
        });
    }

    #[Group('NonCoroutine')]
    public function testProcessDgramSocket()
    {
        $demo = new DemoStub();
        $process = new Process(function (Process $process) use ($demo) {
            $socket = new Socket($process->exportSocket(), new SerializePacker(), SOCK_DGRAM);
            $ret = $socket->recv();
            $this->assertSame($demo->unique, $ret->unique);
            $socket->send('end');
        }, false, SOCK_DGRAM, true);

        $process->start();

        run(function () use ($process, $demo) {
            $socket = new Socket($process->exportSocket(), new SerializePacker(), SOCK_DGRAM);
            $ret = $socket->send($demo);
            $this->assertSame(81, $ret);
            $ret = $socket->recv();
            $this->assertSame('end', $ret);
        });
    }
}
