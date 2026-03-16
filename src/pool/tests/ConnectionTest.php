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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Event\ReleaseConnection;
use Hyperf\Pool\Pool;
use Hyperf\Pool\PoolOption;
use HyperfTest\Pool\Stub\ActiveConnectionStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetActiveConnectionAgain()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive('warning')->withAnyArgs()->once()->andReturnTrue();
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->once()->andReturnTrue();
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->once()->andReturn($logger);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        $connection = new ActiveConnectionStub($container, Mockery::mock(Pool::class));
        $this->assertEquals($connection, $connection->getConnection());
    }

    public function testReleaseConnectionEvent()
    {
        $assert = 0;
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->once()->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($dispatcher = Mockery::mock(EventDispatcherInterface::class));
        $dispatcher->shouldReceive('dispatch')->once()->with(ReleaseConnection::class)->andReturnUsing(function (ReleaseConnection $event) use (&$assert) {
            $assert = $event->connection->getLastReleaseTime();
        });

        $connection = new ActiveConnectionStub($container, $pool = Mockery::mock(Pool::class));
        $pool->shouldReceive('release')->withAnyArgs()->andReturnNull();
        $pool->shouldReceive('getOption')->andReturn(new PoolOption(events: [ReleaseConnection::class]));

        $connection->release();
        $this->assertTrue($assert > 0);
    }

    public function testDontHaveEvents()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->once()->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($dispatcher = Mockery::mock(EventDispatcherInterface::class));
        $dispatcher->shouldReceive('dispatch')->never()->with(ReleaseConnection::class)->andReturnNull();

        $connection = new ActiveConnectionStub($container, $pool = Mockery::mock(Pool::class));
        $pool->shouldReceive('release')->withAnyArgs()->andReturnNull();
        $pool->shouldReceive('getOption')->andReturn(new PoolOption(events: []));

        $connection->release();

        $this->assertTrue(true);
    }
}
