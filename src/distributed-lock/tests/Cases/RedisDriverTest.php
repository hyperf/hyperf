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
use Hyperf\DistributedLock\Mutex;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
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
        Mockery::close();
    }

    public function testLock()
    {
        $container = $this->getContainer();
        $driver    = $container->get(LockManager::class)->getDriver('redis');

        $resource = 'resource';
        $ttl      = 10;

        $mutex = $driver->lock($resource, $ttl);

        $this->assertTrue($mutex instanceof Mutex);
        $this->assertTrue($mutex->acquired());

        $driver->unlock($mutex);
    }

    public function testLockFiled()
    {
        $container = $this->getContainer();
        $driver    = $container->get(LockManager::class)->getDriver('redis');

        $resource = 'resource1';
        $ttl      = 10;

        $mutex = $driver->lock($resource, $ttl);

        $this->assertTrue($mutex instanceof Mutex);
        $this->assertTrue($mutex->acquired());

        $mutex1 = $driver->lock($resource, $ttl);
        $this->assertFalse($mutex1->acquired());

        $driver->unlock($resource, $ttl);
    }

    public function testLockTTL()
    {
        $container = $this->getContainer();
        $driver    = $container->get(LockManager::class)->getDriver('redis');

        $resource = 'resource1';
        $ttl      = 10;

        $mutex = $driver->lock($resource, $ttl);

        $this->assertTrue($mutex instanceof Mutex);
        $this->assertTrue($mutex->acquired());

        sleep(10);

        $mutex1 = $driver->lock($resource, $ttl);
        $this->assertTrue($mutex1->acquired());

        $driver->unlock($resource, $ttl);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        $config    = new Config([
            'distributed-lock' => [
                'prefix'    => 'lock',
                'ttl'       => 10,
                'separator' => ':',
                'driver'    => 'redis',
                'redis'     => [
                    'drift_factor' => 0.01,
                    'retry'        => 0,
                    'retry_delay'  => 200, // time in ms
                    'pools'        => [
                        'db0',
                        'db1',
                        'db2',
                    ],
                ],
            ],
            'redis'            => [
                'db0' => [
                    'host'           => 'localhost',
                    'auth'           => '910123',
                    'port'           => 6379,
                    'db'             => 0,
                    'timeout'        => 0.0,
                    'reserved'       => null,
                    'retry_interval' => 0,
                    'pool'           => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout'    => 3.0,
                        'heartbeat'       => -1,
                        'max_idle_time'   => 60,
                    ],
                ],
                'db1' => [
                    'host'           => 'localhost',
                    'auth'           => '910123',
                    'port'           => 6379,
                    'db'             => 1,
                    'timeout'        => 0.0,
                    'reserved'       => null,
                    'retry_interval' => 0,
                    'pool'           => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout'    => 3.0,
                        'heartbeat'       => -1,
                        'max_idle_time'   => 60,
                    ],
                ],
                'db2' => [
                    'host'           => 'localhost',
                    'auth'           => '910123',
                    'port'           => 6379,
                    'db'             => 2,
                    'timeout'        => 0.0,
                    'reserved'       => null,
                    'retry_interval' => 0,
                    'pool'           => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout'    => 3.0,
                        'heartbeat'       => -1,
                        'max_idle_time'   => 60,
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
