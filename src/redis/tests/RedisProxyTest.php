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

namespace HyperfTest\Redis;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
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
class RedisProxyTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testRedisOptions()
    {
        $redis = $this->getRedis([
            \Redis::OPT_PREFIX => 'test:',
        ]);

        $redis->set('xxx', 'yyy');
        $this->assertSame('yyy', $redis->get('xxx'));

        $this->assertSame('yyy', $this->getRedis()->get('test:xxx'));
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
