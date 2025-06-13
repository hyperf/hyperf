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

namespace HyperfTest\Cache\Stub;

use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Codec\Packer\PhpSerializerPacker;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
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
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Mockery;
use Psr\EventDispatcher\EventDispatcherInterface;

class ContainerStub
{
    public static function getContainer(): Container
    {
        $container = Mockery::mock(Container::class);
        $config = new Config([
            'cache' => [
                'default' => [
                    'driver' => RedisDriver::class,
                    'packer' => PhpSerializerPacker::class,
                    'prefix' => 'c:',
                    'skip_cache_results' => [],
                ],
                'serialize' => [
                    'driver' => SerializeRedisDriver::class,
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
                'serialize' => [
                    'host' => 'localhost',
                    'auth' => null,
                    'port' => 6379,
                    'db' => 0,
                    'timeout' => 0.0,
                    'reserved' => null,
                    'retry_interval' => 0,
                    'options' => [
                        \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
                    ],
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

        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

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
        $container->shouldReceive('get')->with(Redis::class)->andReturn(new Redis($poolFactory));

        $container->shouldReceive('make')->with(RedisProxy::class, Mockery::any())->andReturnUsing(function ($_, $args) use ($poolFactory) {
            return new RedisProxy($poolFactory, $args['pool']);
        });
        $container->shouldReceive('get')->with(RedisFactory::class)->andReturnUsing(function () use ($config) {
            return new RedisFactory($config);
        });
        $container->shouldReceive('make')->with(SerializeRedisDriver::class, Mockery::any())->andReturnUsing(function ($_, $args) use ($container) {
            return new SerializeRedisDriver($container, $args['config']);
        });
        ApplicationContext::setContainer($container);

        return $container;
    }
}
