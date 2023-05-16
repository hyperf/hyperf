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
namespace HyperfTest\Snowflake;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Di\Container;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\RedisProxy;
use Hyperf\Snowflake\Configuration as SnowflakeConfig;
use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Hyperf\Snowflake\Meta;
use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;
use HyperfTest\Snowflake\Stub\UserDefinedIdGenerator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Throwable;

use function Hyperf\Coroutine\go;
use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
class RedisMetaGeneratorTest extends TestCase
{
    protected function tearDown(): void
    {
        $container = $this->getContainer();
        $redis = $container->make(RedisProxy::class, ['pool' => 'snowflake']);
        $redis->del(RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY);
    }

    public function testGenerateMeta()
    {
        $container = $this->getContainer();
        $config = $container->get(ConfigInterface::class);
        $metaGenerator = new RedisMilliSecondMetaGenerator(new SnowflakeConfig(), MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $config);

        $meta = $metaGenerator->generate();
        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertSame(0, $meta->getDataCenterId());
        $this->assertSame(1, $meta->getWorkerId());
    }

    public function testGenerateId()
    {
        $container = $this->getContainer();
        $hConfig = $container->get(ConfigInterface::class);
        $config = new SnowflakeConfig();
        $metaGenerator = new RedisMilliSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $hConfig);
        $generator = new SnowflakeIdGenerator($metaGenerator);

        $id = $generator->generate();
        $this->assertTrue(is_int($id));

        $meta = $generator->degenerate($id);
        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertSame(0, $meta->getDataCenterId());
        $this->assertSame(1, $meta->getWorkerId());
    }

    public function testGenerateMetaSeconds()
    {
        $container = $this->getContainer();
        $hConfig = $container->get(ConfigInterface::class);
        $config = new SnowflakeConfig();
        $metaGenerator = new RedisSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $hConfig);
        $meta = $metaGenerator->generate();
        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertSame(0, $meta->getDataCenterId());
        $this->assertSame(1, $meta->getWorkerId());
    }

    public function testGenerateAndDeGenerateSeconds()
    {
        $container = $this->getContainer();
        $hConfig = $container->get(ConfigInterface::class);
        $config = new SnowflakeConfig();
        $metaGenerator = new RedisSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $hConfig);
        $generator = new SnowflakeIdGenerator($metaGenerator);

        $id = $generator->generate();
        $this->assertTrue(is_int($id));

        $meta = $generator->degenerate($id);
        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertSame(0, $meta->getDataCenterId());
        $this->assertSame(1, $meta->getWorkerId());
    }

    public function testDeGenerateMaxId()
    {
        $container = $this->getContainer();
        $hConfig = $container->get(ConfigInterface::class);
        $config = new SnowflakeConfig();
        $metaGenerator = new RedisSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $hConfig);
        $generator = new SnowflakeIdGenerator($metaGenerator);

        $meta = $generator->degenerate(PHP_INT_MAX);
        $interval = $meta->getTimeInterval();

        $this->assertSame(31, $meta->getDataCenterId());
        $this->assertSame(31, $meta->getWorkerId());
        $this->assertSame(4095, $meta->getSequence());
        $this->assertSame(69730, intval($interval / (3600 * 24 * 365))); // 7W years
    }

    public function testUserDefinedIdGenerator()
    {
        $container = $this->getContainer();
        $hConfig = $container->get(ConfigInterface::class);
        $config = new SnowflakeConfig();
        $metaGenerator = new RedisSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $hConfig);
        $generator = new SnowflakeIdGenerator($metaGenerator);
        $generator = new UserDefinedIdGenerator($generator);

        $userId = 20190620;

        $id = $generator->generate($userId);

        $meta = $generator->degenerate($id);

        $this->assertSame($meta->getWorkerId(), $userId % 31);
    }

    public function testRedisSecondNextTimestamp()
    {
        $container = $this->getContainer();
        $hConfig = $container->get(ConfigInterface::class);
        $config = new SnowflakeConfig();
        $metaGenerator = new RedisSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $hConfig);

        $time = $metaGenerator->getTimestamp();
        $nextTime = $metaGenerator->getNextTimestamp();
        $this->assertSame($time + 1, $nextTime);
    }

    public function testCollisionWhenInitManyTimes()
    {
        $container = $this->getContainer();
        $hConfig = $container->get(ConfigInterface::class);
        $config = new SnowflakeConfig();
        $metaGenerator = new RedisSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $hConfig);
        $result = [];
        $tasks = [];
        for ($i = 0; $i < 2000; ++$i) {
            $tasks[] = static function () use (&$result, $metaGenerator) {
                $result[] = $metaGenerator->getDataCenterId();
            };
        }

        parallel($tasks);

        $this->assertSame(1, count(array_unique($result)));
    }

    public function testGenerateSameMetaForRedisSecond()
    {
        $container = $this->getContainer();
        $hConfig = $container->get(ConfigInterface::class);
        $config = new SnowflakeConfig();
        $metaGenerator = new RedisSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND, $hConfig);
        $generator = new SnowflakeIdGenerator($metaGenerator);
        $result = [];
        $channel = new Channel(2);
        go(function () use (&$result, $generator, $channel) {
            try {
                for ($i = 0; $i < 4100; ++$i) {
                    $result[] = $generator->generate();
                }
            } catch (Throwable $exception) {
            } finally {
                $channel->push(Mockery::mock(ConnectionInterface::class));
            }
        });

        go(function () use (&$result, $generator, $channel) {
            try {
                for ($i = 0; $i < 900; ++$i) {
                    $result[] = $generator->generate();
                }
            } catch (Throwable $exception) {
            } finally {
                $channel->push(Mockery::mock(ConnectionInterface::class));
            }
        });

        $channel->pop(5);
        $channel->pop(5);

        $this->assertSame(5000, count(array_unique($result)));
    }

    protected function getContainer()
    {
        $config = new Config([
            'redis' => [
                'snowflake' => [
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
            'snowflake' => [
                'begin_second' => MetaGeneratorInterface::DEFAULT_BEGIN_SECOND,
                RedisMilliSecondMetaGenerator::class => [
                    'pool' => 'snowflake',
                ],
                RedisSecondMetaGenerator::class => [
                    'pool' => 'snowflake',
                ],
            ],
        ]);

        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new Channel($args['size']);
        });
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn(new Frequency());
        $container->shouldReceive('make')->with(RedisPool::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($container) {
            return new RedisPool($container, $args['name']);
        });
        $factory = new PoolFactory($container);
        $container->shouldReceive('make')->with(RedisProxy::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($factory) {
            return new RedisProxy($factory, $args['pool']);
        });

        return $container;
    }
}
