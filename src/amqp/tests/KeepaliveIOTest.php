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

namespace HyperfTest\Amqp;

use Hyperf\Amqp\Connection\KeepaliveIO;
use Hyperf\Amqp\Connection\Socket;
use Hyperf\Utils\Context;
use HyperfTest\Amqp\Stub\ContainerStub;
use HyperfTest\Amqp\Stub\SocketStub;
use Mockery;
use PhpAmqpLib\Wire\AMQPWriter;
use PHPUnit\Framework\TestCase;
use Swoole\Timer;

/**
 * @internal
 * @coversNothing
 */
class KeepaliveIOTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        Timer::clearAll();
        Context::set('test.amqp.send.data', null);
    }

    public function testKeepaliveConnect()
    {
        $host = '127.0.0.1';
        $port = 5672;
        $timeout = 5;
        $heartbeat = 3;

        $container = ContainerStub::getHyperfContainer();
        $container->shouldReceive('make')->with(Socket::class, Mockery::any())->andReturnUsing(function ($_, $args) use ($host, $port, $timeout, $heartbeat) {
            $this->assertEquals([$host, $port, $timeout, $heartbeat], $args);
            return Mockery::mock(Socket::class);
        });

        $io = new KeepaliveIO($host, $port, $timeout, 6, null, true, $heartbeat);
        $io->connect();
    }

    public function testKeepaliveRead()
    {
        $host = '127.0.0.1';
        $port = 5672;
        $timeout = 5;
        $heartbeat = 3;

        $container = ContainerStub::getHyperfContainer();
        $container->shouldReceive('make')->with(Socket::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new SocketStub(...$args);
        });

        $io = new KeepaliveIO($host, $port, $timeout, 6, null, true, $heartbeat);
        $io->connect();

        $res = $io->read(10);
        $this->assertTrue(strlen($res) === 10);
    }

    public function testKeepaliveHeartbeatTimer()
    {
        $host = '127.0.0.1';
        $port = 5672;
        $timeout = 5;
        $heartbeat = 1;

        $container = ContainerStub::getHyperfContainer();
        $container->shouldReceive('make')->with(Socket::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new SocketStub(...$args);
        });

        $io = new KeepaliveIO($host, $port, $timeout, 6, null, true, $heartbeat);

        $this->assertTrue(count(Timer::list()) === 0);
        $io->connect();
        $this->assertTrue(count(Timer::list()) === 1);

        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);
        $data = $pkt->getvalue();

        $this->assertSame($data, Context::get('test.amqp.send.data'));
    }
}
