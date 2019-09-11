<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\DistributedLock\Cases;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\DistributedLock\Driver\RedisDriver;
use Hyperf\DistributedLock\LockManager;
use Hyperf\Pool\Channel;
use Hyperf\Pool\LowFrequencyInterface;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Cache\Stub\Foo;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RedisDriverTest extends TestCase
{
    protected function tearDown()
    {
        $container = $this->getContainer();
        $driver = $container->get(LockManager::class)->getDriver();
        $driver->clear();

        Mockery::close();
    }

    public function testLock()
    {
        $container = $this->getContainer();
        $driver = $container->get(LockManager::class)->getDriver();

        $this->assertNull($driver->get('xxx', null));
        $this->assertTrue($driver->set('xxx', 'yyy'));
        $this->assertSame('yyy', $driver->get('xxx'));

        $id = uniqid();
        $obj = new Foo($id);
        $driver->set('xxx', $obj);
        $this->assertSame($id, $driver->get('xxx')->id);
    }

    public function testUnlock()
    {
        $container = $this->getContainer();
        $driver = $container->get(LockManager::class)->getDriver();

        [$bool, $result] = $driver->fetch('xxx');
        $this->assertFalse($bool);
        $this->assertNull($result);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        $config = new Config([
            'distributed-lock' => [
            ],
            'redis' => [
                'db0' => [
                    'host' => 'localhost',
                    'auth' => '910123',
                    'port' => 6379,
                    'db' => 0,
                    'timeout' => 0.0,
                    'reserved' => null,
                    'retry_interval' => 0,
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60,
                    ],
                ],
                'db1' => [
                    'host' => 'localhost',
                    'auth' => '910123',
                    'port' => 6379,
                    'db' => 1,
                    'timeout' => 0.0,
                    'reserved' => null,
                    'retry_interval' => 0,
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60,
                    ],
                ],
                'db2' => [
                    'host' => 'localhost',
                    'auth' => '910123',
                    'port' => 6379,
                    'db' => 2,
                    'timeout' => 0.0,
                    'reserved' => null,
                    'retry_interval' => 0,
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60,
                    ],
                ],
            ],
        ]);

        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive(Mockery::any())->andReturn(null);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(LockManager::class)->andReturn(new LockManager($config, $logger));
        $container->shouldReceive('make')->with(RedisDriver::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($container) {
            return new RedisDriver($container, $args['config']);
        });
        $container->shouldReceive('get')->with(PhpSerializerPacker::class)->andReturn(new PhpSerializerPacker());
        $frequency = Mockery::mock(LowFrequencyInterface::class);
        $frequency->shouldReceive('isLowFrequency')->andReturn(true);
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn($frequency);
        $container->shouldReceive('make')->with(RedisPool::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($container) {
            return new RedisPool($container, $args['name']);
        });
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new Channel($args['size']);
        });

        $poolFactory = new PoolFactory($container);
        $container->shouldReceive('get')->with(\Redis::class)->andReturn(new Redis($poolFactory));

        ApplicationContext::setContainer($container);

        return $container;
    }
}
