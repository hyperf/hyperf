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

namespace HyperfTest\Cache\Cases;

use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Pool\Channel;
use Hyperf\Pool\LowFrequencyInterface;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Packer\PhpSerializerPacker;
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
        $driver = $container->get(CacheManager::class)->getDriver();
        $driver->clear();

        Mockery::close();
    }

    public function testSetAndGet()
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();

        $this->assertNull($driver->get('xxx', null));
        $this->assertTrue($driver->set('xxx', 'yyy'));
        $this->assertSame('yyy', $driver->get('xxx'));

        $id = uniqid();
        $obj = new Foo($id);
        $driver->set('xxx', $obj);
        $this->assertSame($id, $driver->get('xxx')->id);
    }

    public function testFetch()
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();

        [$bool, $result] = $driver->fetch('xxx');
        $this->assertFalse($bool);
        $this->assertNull($result);
    }

    public function testExpiredTime()
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();

        $driver->set('xxx', 'yyy', 1);
        [$bool, $result] = $driver->fetch('xxx');
        $this->assertTrue($bool);
        $this->assertSame('yyy', $result);

        $redis = $container->get(\Redis::class);
        $this->assertSame(1, $redis->ttl('c:xxx'));
    }

    public function testDelete()
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();

        $driver->set('xxx', 'yyy');
        $driver->set('xxx2', 'yyy');
        $driver->set('xxx3', 'yyy');

        $driver->deleteMultiple(['xxx', 'xxx2']);

        $this->assertNull($driver->get('xxx'));
        $this->assertNotNull($driver->get('xxx3'));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        $config = new Config([
            'cache' => [
                'default' => [
                    'driver' => RedisDriver::class,
                    'packer' => PhpSerializerPacker::class,
                    'prefix' => 'c:',
                ],
            ],
            'redis' => [
                'default' => [
                    'host' => 'localhost',
                    'auth' => null,
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
            ],
        ]);

        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive(Mockery::any())->andReturn(null);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(CacheManager::class)->andReturn(new CacheManager($config, $logger));
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
