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
namespace HyperfTest\Redis;

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
use Hyperf\Utils\Context;
use HyperfTest\Redis\Stub\RedisPoolFailedStub;
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
        Context::set('redis.connection.default', null);
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

    public function testRedisReuseAfterThrowable()
    {
        $container = $this->getContainer();
        $pool = new RedisPoolFailedStub($container, 'default');
        $container->shouldReceive('make')->once()->with(RedisPool::class, ['name' => 'default'])->andReturn($pool);
        $factory = new PoolFactory($container);
        $redis = new Redis($factory);
        try {
            $redis->set('xxxx', 'yyyy');
        } catch (\Throwable $exception) {
            $this->assertSame('Get connection failed.', $exception->getMessage());
        }

        $this->assertSame(1, $pool->getConnectionsInChannel());
        $this->assertSame(1, $pool->getCurrentConnections());
    }

    private function getRedis()
    {
        $container = $this->getContainer();
        $pool = new RedisPoolStub($container, 'default');
        $container->shouldReceive('make')->once()->with(RedisPool::class, ['name' => 'default'])->andReturn($pool);
        $factory = new PoolFactory($container);

        return new Redis($factory);
    }

    private function getContainer()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

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
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn(new Frequency());
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new Channel($args['size']);
        });
        return $container;
    }
}
