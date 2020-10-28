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

namespace HyperfTest\Cases;

use Hyperf\Lock\IdGenerateInterface;
use Hyperf\Snowflake\Configuration;
use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Hyperf\Snowflake\MetaGenerator\RandomMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Cases\Stub\RedisPoolStub;
use Hyperf\Lock\Factory\LockFactory;
use Hyperf\Lock\Factory\RedisLock;
use Mockery;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    private function getRedis()
    {
        $container = $this->getRedisContainer();
        $pool = new RedisPoolStub($container, 'default');
        $container->shouldReceive('make')->with(RedisPool::class, ['name' => 'default'])->andReturn($pool);
        $factory = new PoolFactory($container);

        return new Redis($factory);
    }

    private function getRedisContainer()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(
            new Config(
                [
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
                ]
            )
        );
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn(new Frequency());
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(
            function ($class, $args) {
                return new PoolOption(...array_values($args));
            }
        );
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(
            function ($class, $args) {
                return new Channel($args['size']);
            }
        );
        return $container;
    }

    protected function getContainer()
    {
        $container = \Mockery::mock(Container::class);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(
            new Config(
                [
                    'lock' => [
                        'default' => [
                            'driver' => 'redis',
                            // lock expired time (millisecond)
                            'lock_expired' => 100,
                            // with retry lock time (millisecond)
                            'with_time' => 300,
                            'retry' => 5,
                        ],
                    ],
                ]
            )
        );

        $container->shouldReceive('get')->with(Redis::class)->andReturn($this->getRedis());

        $config = new Configuration();
        $generator = new SnowflakeIdGenerator(new RandomMilliSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND));
        $container->shouldReceive('get')->with(IdGenerateInterface::class)->andReturn($generator);

        $container->shouldReceive('make')->with(RedisLock::class, Mockery::any())->andReturnUsing(
            function ($_, $args) {
                return new RedisLock(...array_values($args));
            }
        );

        $container->shouldReceive('get')->with(LockFactory::class)->andReturn(new LockFactory($container));

        ApplicationContext::setContainer($container);

        return $container;
    }

}
