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

    protected function setUp(): void
    {
        parent::setUp();

        $prefixProperty = new ReflectionProperty(Redis::class, 'prefix');
        $prefixProperty->setAccessible(true);

        $this->prePrefix = $prefixProperty->getDefaultValue();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Redis::setPrefix($this->prePrefix);

        Mockery::close();
    }

    public function testEmptyMetricRedisConfig()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([]));
        $container->shouldReceive('get')->with(\Redis::class)->andReturn(new \Redis());

        $factory = new RedisStorageFactory();
        $redis = $factory($container);

        $prefixProperty = new ReflectionProperty(Redis::class, 'prefix');
        $prefixProperty->setAccessible(true);

        self::assertInstanceOf(Redis::class, $redis);
        self::assertEquals('skeleton', $prefixProperty->getValue($redis));
    }
}
