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

namespace HyperfTest\Redis;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Pool\Channel;
use Hyperf\Pool\LowFrequencyInterface;
use Hyperf\Pool\Pool;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\Redis\Stub\RedisConnectionStub;
use HyperfTest\Redis\Stub\RedisPoolStub;
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
class RedisConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testRedisConnectionConfig()
    {
        $pool = $this->getRedisPool();

        $config = $pool->get()->getConfig();

        $this->assertSame([
            'host' => 'redis',
            'port' => 16379,
            'auth' => 'redis',
            'db' => 0,
            'timeout' => 0.0,
            'reserved' => null,
            'retry_interval' => 5,
            'read_timeout' => 3.0,
            'cluster' => [
                'enable' => false,
                'name' => null,
                'seeds' => [
                    '127.0.0.1:6379',
                ],
                'read_timeout' => 0.0,
                'persistent' => false,
                'context' => [
                    'stream' => ['cafile' => 'foo-cafile', 'verify_peer' => true],
                ],
            ],
            'sentinel' => [
                'enable' => false,
                'master_name' => '',
                'nodes' => [],
                'persistent' => '',
                'read_timeout' => 0,
            ],
            'options' => [],
            'context' => [
                'stream' => ['cafile' => 'foo-cafile', 'verify_peer' => true],
            ],
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 30,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'max_idle_time' => 1,
            ],
        ], $config);
    }

    public function testRedisPoolConfig()
    {
        $pool = $this->getRedisPool();

        $config = $pool->getConfig();

        $this->assertSame($this->getDefaultPoolConfig(), $config);
    }

    public function testRedisConnectionReconnect()
    {
        $pool = $this->getRedisPool();

        $connection = $pool->get()->getConnection();
        $this->assertSame(null, $connection->getDatabase());

        $connection->setDatabase(2);
        $connection->reconnect();
        $this->assertSame(2, $connection->getDatabase());

        $connection->release();
        $connection = $pool->get()->getConnection();
        $this->assertSame(null, $connection->getDatabase());
    }

    public function testRedisConnectionLog()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn($logger = Mockery::mock(StdoutLoggerInterface::class));
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        $logger->shouldReceive('log')->once();

        $conn = new RedisConnectionStub($container, Mockery::mock(Pool::class), []);
        $conn = new ClassInvoker($conn);
        $conn->log('xxxx');
        $this->assertTrue(true);
    }

    public function testRedisCloseInLowFrequency()
    {
        $pool = $this->getRedisPool();

        $connection1 = $pool->get()->getConnection();
        $connection2 = $pool->get()->getConnection();
        $connection3 = $pool->get()->getConnection();

        $this->assertSame(3, $pool->getCurrentConnections());

        $connection1->release();
        $connection2->release();
        $connection3->release();

        $this->assertSame(3, $pool->getCurrentConnections());

        $connection = $pool->get()->getConnection();

        $this->assertSame(1, $pool->getCurrentConnections());

        $connection->release();
    }

    private function getDefaultPoolConfig()
    {
        return [
            'host' => 'redis',
            'auth' => 'redis',
            'port' => 16379,
            'read_timeout' => 3.0,
            'reserved' => null,
            'retry_interval' => 5,
            'context' => [
                'stream' => ['cafile' => 'foo-cafile', 'verify_peer' => true],
            ],
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 30,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'max_idle_time' => 1,
            ],
            'cluster' => [
                'enable' => false,
                'name' => null,
                'seeds' => [
                    '127.0.0.1:6379',
                ],
                'context' => [
                    'stream' => ['cafile' => 'foo-cafile', 'verify_peer' => true],
                ],
            ],
            'sentinel' => [
                'enable' => false,
            ],
        ];
    }

    private function getRedisPool()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'redis' => [
                'default' => $this->getDefaultPoolConfig(),
            ],
        ]));

        $frequency = Mockery::mock(LowFrequencyInterface::class);
        $frequency->shouldReceive('isLowFrequency')->andReturn(true);
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn($frequency);
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new Channel($args['size']);
        });

        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        ApplicationContext::setContainer($container);

        return new RedisPoolStub($container, 'default');
    }
}
