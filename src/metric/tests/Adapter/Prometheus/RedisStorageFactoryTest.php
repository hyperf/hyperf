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
namespace HyperfTest\Metric\Adapter\Prometheus;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Metric\Adapter\Prometheus\Redis;
use Hyperf\Metric\Adapter\Prometheus\RedisStorageFactory;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionProperty;

/**
 * @internal
 * @coversNothing
 */
class RedisStorageFactoryTest extends TestCase
{
    protected string $prePrefix;

    protected string $preMetricGatherKeySuffix;

    protected function setUp(): void
    {
        parent::setUp();

        $prefixProperty = new ReflectionProperty(Redis::class, 'prefix');
        $prefixProperty->setAccessible(true);

        $metricGatherKeySuffix = new ReflectionProperty(Redis::class, 'metricGatherKeySuffix');
        $metricGatherKeySuffix->setAccessible(true);

        $this->prePrefix = $prefixProperty->getDefaultValue();
        $this->preMetricGatherKeySuffix = $metricGatherKeySuffix->getDefaultValue();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Redis::setPrefix($this->prePrefix);
        Redis::setMetricGatherKeySuffix($this->preMetricGatherKeySuffix);

        Mockery::close();
    }

    public function testEmptyMetricRedisConfig()
    {
        $redisFactory = Mockery::mock(RedisFactory::class);
        $redisFactory->shouldReceive('get')->with('default')->times(1)->andReturn(Mockery::mock(RedisProxy::class));

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->times(1)->andReturn(new Config([]));
        $container->shouldReceive('get')->with(RedisFactory::class)->times(1)->andReturn($redisFactory);

        $factory = new RedisStorageFactory();
        $redis = $factory($container);

        $prefixProperty = new ReflectionProperty(Redis::class, 'prefix');
        $prefixProperty->setAccessible(true);

        $metricGatherKeySuffixProperty = new ReflectionProperty(Redis::class, 'metricGatherKeySuffix');
        $metricGatherKeySuffixProperty->setAccessible(true);

        self::assertInstanceOf(Redis::class, $redis);
        self::assertEquals('skeleton', $prefixProperty->getValue($redis));
        self::assertEquals('_METRIC_KEYS', $metricGatherKeySuffixProperty->getValue($redis));
    }

    public function testNewConfig()
    {
        $redisFactory = Mockery::mock(RedisFactory::class);
        $redisFactory->shouldReceive('get')->with('default')->andReturn(Mockery::mock(RedisProxy::class));

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'metric' => [
                'metric' => [
                    'prometheus' => [
                        'redis_config' => 'default',
                        'redis_prefix' => 'prometheus:',
                        'redis_gather_key_suffix' => ':metric_keys',
                    ],
                ],
            ],
        ]));
        $container->shouldReceive('get')->with(RedisFactory::class)->andReturn($redisFactory);

        $factory = new RedisStorageFactory();
        $redis = $factory($container);

        $prefixProperty = new ReflectionProperty(Redis::class, 'prefix');
        $prefixProperty->setAccessible(true);

        $metricGatherKeySuffixProperty = new ReflectionProperty(Redis::class, 'metricGatherKeySuffix');
        $metricGatherKeySuffixProperty->setAccessible(true);

        self::assertInstanceOf(Redis::class, $redis);
        self::assertEquals('prometheus:', $prefixProperty->getValue($redis));
        self::assertEquals(':metric_keys', $metricGatherKeySuffixProperty->getValue($redis));
    }
}
