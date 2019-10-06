<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace HyperfTest\Redis;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Redis\Stub\RedisPoolStub;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RedisTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testRedisConnect()
    {
        $redis = new \Redis();
        $class = new \ReflectionClass($redis);
        $params = $class->getMethod('connect')->getParameters();
        [$host, $port, $timeout, $retryInterval] = $params;
        $this->assertSame('host', $host->getName());
        $this->assertSame('port', $port->getName());
        $this->assertSame('timeout', $timeout->getName());
        $this->assertSame('retry_interval', $retryInterval->getName());

        $this->assertTrue($redis->connect('127.0.0.1', 6379, 0.0));
    }

    public function testRedisSelect()
    {
        $redis = $this->getRedis();

        $res = $redis->set('xxxx', 'yyyy');
        $this->assertSame('db:0 name:set argument:xxxx,yyyy', $res);

        $redis->select(2);
        $res = $redis->get('xxxx');
        $this->assertSame('db:2 name:get argument:xxxx', $res);

        $this->assertSame(2, $redis->getDatabase());

        $res = parallel([function () use ($redis) {
            return $redis->get('xxxx');
        }]);

        $this->assertSame('db:0 name:get argument:xxxx', $res[0]);
    }

    private function getRedis()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'redis' => [
                'default' => [
                    'host' => 'localhost',
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
        $pool = new RedisPoolStub($container, 'default');
        $container->shouldReceive('make')->once()->with(RedisPool::class, ['name' => 'default'])->andReturn($pool);

        ApplicationContext::setContainer($container);

        $factory = new PoolFactory($container);

        return new Redis($factory);
    }
}
