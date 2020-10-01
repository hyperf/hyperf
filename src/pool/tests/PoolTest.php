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
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Pool\Stub\FooPool;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class PoolTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testPoolFlush()
    {
        $container = $this->getContainer();
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(value(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('error')->withAnyArgs()->times(4)->andReturn(true);
            return $logger;
        }));
        $pool = new FooPool($container, []);

        $conns = [];
        for ($i = 0; $i < 5; ++$i) {
            $conns[] = $pool->get();
        }

        foreach ($conns as $conn) {
            $pool->release($conn);
        }

        $pool->flush();
        $this->assertSame(1, $pool->getConnectionsInChannel());
        $this->assertSame(1, $pool->getCurrentConnections());
    }

    public function testPoolFlushOne()
    {
        $container = $this->getContainer();
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(value(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('error')->withAnyArgs()->times(3)->andReturn(true);
            return $logger;
        }));
        $pool = new FooPool($container, []);

        $conns = [];
        $checks = [false, false, true, true, true];
        for ($i = 0; $i < 5; ++$i) {
            $conn = $pool->get();
            $conn->shouldReceive('check')->andReturn(array_shift($checks));
            $conns[] = $conn;
        }

        foreach ($conns as $conn) {
            $pool->release($conn);
        }

        $pool->flushOne();
        $this->assertSame(4, $pool->getConnectionsInChannel());
        $this->assertSame(4, $pool->getCurrentConnections());
        $pool->flushOne(true);
        $this->assertSame(3, $pool->getConnectionsInChannel());
        $this->assertSame(3, $pool->getCurrentConnections());
        $pool->flushOne(true);
        $this->assertSame(2, $pool->getConnectionsInChannel());
        $this->assertSame(2, $pool->getCurrentConnections());
        $pool->flushOne();
        $this->assertSame(2, $pool->getConnectionsInChannel());
        $this->assertSame(2, $pool->getCurrentConnections());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
