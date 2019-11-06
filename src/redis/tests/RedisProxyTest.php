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

namespace HyperfTest\Redis;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Pool\Channel;
use Hyperf\Pool\LowFrequencyInterface;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
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
class RedisProxyTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();

        $redis = $this->getRedis();
        $redis->del('test');
        $redis->del('test:test');
    }

    public function testRedisOptionPrefix()
    {
        $redis = $this->getRedis([
            \Redis::OPT_PREFIX => 'test:',
        ]);

        $redis->set('test', 'yyy');
        $this->assertSame('yyy', $redis->get('test'));

        $this->assertSame('yyy', $this->getRedis()->get('test:test'));
    }

    public function testRedisOptionSerializer()
    {
        $redis = $this->getRedis([
            \Redis::OPT_SERIALIZER => (string) \Redis::SERIALIZER_PHP,
        ]);

        $redis->set('test', 'yyy');
        $this->assertSame('yyy', $redis->get('test'));

        $this->assertSame('s:3:"yyy";', $this->getRedis()->get('test'));
    }

    /**
     * @param mixed $optinos
     * @return \Redis
     */
    private function getRedis($optinos = [])
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'redis' => [
                'default' => [
                    'host' => 'localhost',
                    'auth' => null,
                    'port' => 6379,
                    'cluster' => false,
                    'db' => 0,
                    'options' => $optinos,
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
        $pool = new RedisPool($container, 'default');
        $frequency = Mockery::mock(LowFrequencyInterface::class);
        $frequency->shouldReceive('isLowFrequency')->andReturn(false);
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn($frequency);
        $container->shouldReceive('make')->with(RedisPool::class, ['name' => 'default'])->andReturn($pool);
        $container->shouldReceive('make')->with(Channel::class, ['size' => 30])->andReturn(new Channel(30));
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        ApplicationContext::setContainer($container);

        $factory = new PoolFactory($container);

        return new Redis($factory);
    }
}
