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

namespace HyperfTest\Redis\Stub;

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
use Mockery;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Support\value;

class ContainerStub
{
    public static function mockContainer()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'redis' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'auth' => null,
                    'port' => 6379,
                    'db' => 0,
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 30,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60,
                    ],
                ],
            ],
        ]));
        $frequency = Mockery::mock(LowFrequencyInterface::class);
        $frequency->shouldReceive('isLowFrequency')->andReturn(false);
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn($frequency);
        $container->shouldReceive('make')->with(RedisPool::class, ['name' => 'default'])->andReturnUsing(function () use ($container) {
            return new RedisPool($container, 'default');
        });
        $container->shouldReceive('make')->with(Channel::class, ['size' => 30])->andReturn(new Channel(30));
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('has')->with(\Redis::class)->andReturn(true);
        $container->shouldReceive('get')->with(\Redis::class)->andReturnUsing(function () use ($container) {
            $factory = new PoolFactory($container);
            return new Redis($factory);
        });
        $container->shouldReceive('has')->with(Redis::class)->andReturn(true);
        $container->shouldReceive('get')->with(Redis::class)->andReturnUsing(function () use ($container) {
            $factory = new PoolFactory($container);
            return new Redis($factory);
        });
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(value(function () {
            return Mockery::mock(StdoutLoggerInterface::class);
        }));
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        ApplicationContext::setContainer($container);
        return $container;
    }
}
