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

namespace HyperfTest\RpcMultiplex\Cases;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Channel;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcMultiplex\TcpServer;
use Hyperf\RpcServer\RequestDispatcher;
use Hyperf\Server\Connection as HyperfConnection;
use Hyperf\Server\Event;
use Hyperf\Server\Exception\InvalidArgumentException;
use Hyperf\Server\Server;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\RpcMultiplex\Stub\ContainerStub;
use Mockery;
use Multiplex\Contract\PackerInterface;
use Multiplex\Packet;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class TcpServerTest extends AbstractTestCase
{
    public function testInitServerConfig()
    {
        $container = ContainerStub::mockContainer();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'server' => [
                'servers' => [
                    [
                        'name' => 'rpc',
                        'type' => Server::SERVER_BASE,
                        'host' => '0.0.0.0',
                        'port' => 9502,
                        'sock_type' => SWOOLE_SOCK_TCP,
                        'callbacks' => [
                            Event::ON_RECEIVE => [TcpServer::class, 'onReceive'],
                        ],
                        'settings' => $settings = [
                            'open_length_check' => true,
                            'package_max_length' => 1024 * 1024 * 2,
                            'package_length_type' => 'N',
                            'package_length_offset' => 0,
                            'package_body_offset' => 4,
                        ],
                    ],
                ],
            ],
        ]));

        $invoker = new ClassInvoker(new TcpServer(
            $container,
            Mockery::mock(RequestDispatcher::class),
            Mockery::mock(ExceptionHandlerDispatcher::class),
            Mockery::mock(ProtocolManager::class),
            Mockery::mock(StdoutLoggerInterface::class),
        ));

        $this->assertSame($settings, $invoker->initServerConfig('rpc')['settings']);
    }

    public function testHeartbeat()
    {
        $container = ContainerStub::mockContainer();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'server' => [
                'servers' => [
                    [
                        'name' => 'rpc',
                        'type' => Server::SERVER_BASE,
                        'host' => '0.0.0.0',
                        'port' => 9502,
                        'sock_type' => SWOOLE_SOCK_TCP,
                        'callbacks' => [
                            Event::ON_RECEIVE => [TcpServer::class, 'onReceive'],
                        ],
                        'settings' => $settings = [
                            'open_length_check' => true,
                            'package_max_length' => 1024 * 1024 * 2,
                            'package_length_type' => 'N',
                            'package_length_offset' => 0,
                            'package_body_offset' => 4,
                        ],
                    ],
                ],
            ],
        ]));

        /** @var PackerInterface $packer */
        $packer = $container->get(PackerInterface::class);
        $server = new TcpServer(
            $container,
            Mockery::mock(RequestDispatcher::class),
            Mockery::mock(ExceptionHandlerDispatcher::class),
            Mockery::mock(ProtocolManager::class),
            Mockery::mock(StdoutLoggerInterface::class),
        );

        $connection = Mockery::mock(HyperfConnection::class);
        $chan = new Channel(1);
        $connection->shouldReceive('send')->once()->withAnyArgs()->andReturnUsing(function ($args) use ($chan) {
            $chan->push($args);
        });
        $server->onReceive($connection, 1, 1, $packer->pack(new Packet(0, Packet::PING)));

        $packet = $packer->unpack($chan->pop());
        $this->assertSame(0, $packet->getId());
        $this->assertSame(Packet::PONG, $packet->getBody());
        $this->assertTrue($packet->isHeartbeat());
    }

    public function testInitServerConfigFailed()
    {
        $container = ContainerStub::mockContainer();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'server' => [
                'servers' => [
                    [
                        'name' => 'rpc',
                        'type' => Server::SERVER_BASE,
                        'host' => '0.0.0.0',
                        'port' => 9502,
                        'sock_type' => SWOOLE_SOCK_TCP,
                        'callbacks' => [
                            Event::ON_RECEIVE => [TcpServer::class, 'onReceive'],
                        ],
                        'settings' => $settings = [
                            'open_length_check' => true,
                            'package_max_length' => 1024 * 1024 * 2,
                            'package_length_type' => 'N',
                            'package_length_offset' => 0,
                            'package_body_offset' => 8,
                        ],
                    ],
                ],
            ],
        ]));

        $invoker = new ClassInvoker(new TcpServer(
            $container,
            Mockery::mock(RequestDispatcher::class),
            Mockery::mock(ExceptionHandlerDispatcher::class),
            Mockery::mock(ProtocolManager::class),
            Mockery::mock(StdoutLoggerInterface::class),
        ));

        $this->expectException(InvalidArgumentException::class);
        $invoker->initServerConfig('rpc');
    }
}
