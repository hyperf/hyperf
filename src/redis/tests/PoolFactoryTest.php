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
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use HyperfTest\Redis\Stub\RedisPoolStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PoolFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        Mockery::close();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testFlushAll()
    {
        $container = $this->getContainer();

        $factory = new PoolFactory($container);

        $pool1 = $factory->getPool('default');
        $pool2 = $factory->getPool('cache');

        $conn1 = $pool1->get();
        $conn2 = $pool1->get();
        $conn3 = $pool2->get();

        $pool1->release($conn1);
        $pool1->release($conn2);
        $pool2->release($conn3);

        $this->assertSame(2, $pool1->getConnectionsInChannel());
        $this->assertSame(1, $pool2->getConnectionsInChannel());

        $factory->flushAll();

        $this->assertSame(0, $pool1->getConnectionsInChannel());
        $this->assertSame(0, $pool2->getConnectionsInChannel());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'redis' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 6379,
                    'auth' => null,
                    'db' => 0,
                    'timeout' => 0.0,
                    'options' => [],
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60.0,
                    ],
                ],
                'cache' => [
                    'host' => 'localhost',
                    'port' => 6379,
                    'auth' => null,
                    'db' => 1,
                    'timeout' => 0.0,
                    'options' => [],
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60.0,
                    ],
                ],
            ],
        ]));

        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturn(false);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturn(false);
        $container->shouldReceive('has')->with(Frequency::class)->andReturn(false);
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new Channel($args['size']);
        });
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...$args);
        });
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn(new Frequency());
        $container->shouldReceive('make')->with(RedisPool::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($container) {
            return new RedisPoolStub($container, $args['name']);
        });

        return $container;
    }
}
