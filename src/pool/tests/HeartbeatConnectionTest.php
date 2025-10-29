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

namespace HyperfTest\Pool;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\Pool\Stub\HeartbeatPoolStub;
use HyperfTest\Pool\Stub\KeepaliveConnectionStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class HeartbeatConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set('test.pool.heartbeat_connection', []);
    }

    public function testConnectionConstruct()
    {
        $container = $this->getContainer();
        $pool = $container->get(HeartbeatPoolStub::class);
        $connection = $pool->get();

        $this->assertInstanceOf(KeepaliveConnectionStub::class, $connection);
        $this->assertSame(1, $pool->getCurrentConnections());
        $this->assertSame(0, $pool->getConnectionsInChannel());

        $connection = $pool->get();
        $this->assertSame(2, $pool->getCurrentConnections());
        $this->assertSame(0, $pool->getConnectionsInChannel());

        $connection->release();
        $this->assertSame(1, $pool->getConnectionsInChannel());

        $connection = $pool->get();
        $this->assertSame(0, $pool->getConnectionsInChannel());
        $this->assertSame(2, $pool->getCurrentConnections());
    }

    public function testConnectionCall()
    {
        $container = $this->getContainer();
        $pool = $container->get(HeartbeatPoolStub::class);
        /** @var KeepaliveConnectionStub $connection */
        $connection = $pool->get();
        $connection->setActiveConnection($conn = new class {
            public function send(string $data)
            {
                return str_repeat($data, 2);
            }
        });
        $str = uniqid();
        $result = $connection->call(function ($connection) use ($str) {
            return $connection->send($str);
        });

        $this->assertSame($result, str_repeat($str, 2));
    }

    public function testConnectionHeartbeat()
    {
        $container = $this->getContainer();
        $pool = $container->get(HeartbeatPoolStub::class);
        /** @var KeepaliveConnectionStub $connection */
        $connection = $pool->get();
        $connection->reconnect();
        $timer = $connection->timer;
        $this->assertSame(1, count((new ClassInvoker($timer))->closures));
        $this->assertTrue($connection->check());
        $connection->close();
        $this->assertSame(0, count((new ClassInvoker($timer))->closures));
        $this->assertFalse($connection->check());
        $this->assertSame('close protocol', Context::get('test.pool.heartbeat_connection')['close']);
    }

    public function testConnectionDestruct()
    {
        $container = $this->getContainer();
        $pool = $container->get(HeartbeatPoolStub::class);
        /** @var KeepaliveConnectionStub $connection */
        $connection = $pool->get();
        $connection->reconnect();
        $connection->release();

        $connection = $pool->get();
        $connection->reconnect();
        $connection->release();

        $pool->flush();

        $this->assertSame('close protocol', Context::get('test.pool.heartbeat_connection')['close']);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(HeartbeatPoolStub::class)->andReturnUsing(function () use ($container) {
            return new HeartbeatPoolStub($container, []);
        });
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new Channel(...array_values($args));
        });
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new PoolOption(...array_values($args));
        });

        return $container;
    }
}
