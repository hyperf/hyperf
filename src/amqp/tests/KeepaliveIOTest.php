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
namespace HyperfTest\Amqp;

use Hyperf\Amqp\Connection\KeepaliveIO;
use Hyperf\Amqp\Connection\Socket;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use HyperfTest\Amqp\Stub\ContainerStub;
use HyperfTest\Amqp\Stub\SocketStub;
use HyperfTest\Amqp\Stub\SocketWithoutIOStub;
use Mockery;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\AMQPWriter;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;
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
            $this->assertEquals(['host' => $host, 'port' => $port, 'timeout' => $timeout, 'heartbeat' => $heartbeat], $args);
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
            return new SocketStub(...array_values($args));
        });

        $io = new KeepaliveIO($host, $port, $timeout, 6, null, true, $heartbeat);
        $io->connect();

        $res = $io->read(10);
        $this->assertTrue(strlen($res) === 10);
    }

    public function testKeepalivePopFailed()
    {
        $host = '127.0.0.1';
        $port = 5672;
        $timeout = 5;
        $heartbeat = 1;

        $container = ContainerStub::getHyperfContainer();
        $container->shouldReceive('make')->with(Socket::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new SocketWithoutIOStub(true, ...array_values($args));
        });

        $io = new KeepaliveIO($host, $port, $timeout, 6, null, true, $heartbeat);

        $io->connect();

        $this->expectException(AMQPRuntimeException::class);
        $this->expectExceptionMessageRegExp('/^Socket of keepaliveIO is exhausted\. Cannot establish new socket before wait_timeout\.$/');
        $io->read(10);
    }

    public function testKeepaliveHeartbeatTimer()
    {
        $host = '127.0.0.1';
        $port = 5672;
        $timeout = 5;
        $heartbeat = 1;

        $container = ContainerStub::getHyperfContainer();
        $container->shouldReceive('make')->with(Socket::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new SocketStub(...array_values($args));
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

    public function testKeepaliveReconnect()
    {
        $host = '127.0.0.1';
        $port = 5672;
        $timeout = 5;
        $heartbeat = 1;

        $sock = new SocketWithoutIOStub(false, $host, $port, $timeout, $heartbeat);
        $container = ContainerStub::getHyperfContainer();
        $container->shouldReceive('make')->with(Socket::class, Mockery::any())->andReturnUsing(function ($_, $args) use ($sock) {
            return $sock;
        });

        $io = new KeepaliveIO($host, $port, $timeout, 6, null, true, $heartbeat);

        $io->connect();

        try {
            $io->read(10);
        } catch (\Throwable $throwable) {
            $this->assertSame(2, $sock->getConnectCount());
        }
    }

    public function testKeepaliveIOHeartbeat()
    {
        $host = '127.0.0.1';
        $port = 5672;
        $timeout = 5;
        $heartbeat = 1;

        $sock = new SocketWithoutIOStub(true, $host, $port, $timeout, $heartbeat);
        $container = ContainerStub::getHyperfContainer();
        $container->shouldReceive('make')->with(Socket::class, Mockery::any())->andReturnUsing(function ($_, $args) use ($sock) {
            return $sock;
        });
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('error')->once()->with(Mockery::any())->andReturnUsing(function ($message) {
                $this->assertTrue(Str::contains($message, 'KeepaliveIO heartbeat failed'));
                $this->assertTrue(Str::contains($message, 'AMQPRuntimeException'));
                $this->assertTrue(Str::contains($message, 'Socket of keepaliveIO is exhausted.'));
            });

            return $logger;
        });

        $io = new KeepaliveIO($host, $port, $timeout, 6, null, true, $heartbeat);

        $io->connect();

        Coroutine::sleep(2);
    }
}
