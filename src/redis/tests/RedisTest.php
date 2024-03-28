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
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Container;
use Hyperf\Engine\Channel as Chan;
use Hyperf\Pool\Channel;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisProxy;
use HyperfTest\Redis\Stub\RedisPoolFailedStub;
use HyperfTest\Redis\Stub\RedisPoolStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RedisCluster;
use RedisSentinel;
use ReflectionClass;
use Throwable;

use function Hyperf\Coroutine\defer;
use function Hyperf\Coroutine\go;
use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class RedisTest extends TestCase
{
    protected bool $isOlderThan6 = false;

    protected function setUp(): void
    {
        $this->isOlderThan6 = version_compare(phpversion('redis'), '6.0.0', '<');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Context::set('redis.connection.default', null);
    }

    public function testRedisConnect()
    {
        $redis = new \Redis();
        $class = new ReflectionClass($redis);
        $params = $class->getMethod('connect')->getParameters();
        [$host, $port, $timeout, $retryInterval] = $params;
        $this->assertSame('host', $host->getName());
        $this->assertSame('port', $port->getName());
        $this->assertSame('timeout', $timeout->getName());
        if ($this->isOlderThan6) {
            $this->assertSame('retry_interval', $retryInterval->getName());
        } else {
            $this->assertSame('persistent_id', $retryInterval->getName());
        }

        $this->assertTrue($redis->connect('127.0.0.1', 6379, 0.0, null, 0, 0));
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

        $res = parallel([
            function () use ($redis) {
                return $redis->get('xxxx');
            },
        ]);

        $this->assertSame('db:0 name:get argument:xxxx', $res[0]);
    }

    public function testHasAlreadyBeenBoundToAnotherCoroutine()
    {
        $chan = new Chan(1);
        $redis = $this->getRedis();
        $ref = new ReflectionClass($redis);
        $method = $ref->getMethod('getConnection');

        go(function () use ($chan, $redis, $method) {
            $id = null;
            defer(function () use ($redis, $chan, &$id, $method) {
                $chan->push(true);
                Coroutine::sleep(0.01);
                $hasContextConnection = Context::has('redis.connection.default');
                $this->assertFalse($hasContextConnection);
                $connection = $method->invoke($redis, [$hasContextConnection]);
                $this->assertNotEquals($connection->id, $id);
                $redis->getConnection();
                $chan->push(true);
            });

            $redis->keys('*');

            $hasContextConnection = Context::has('redis.connection.default');
            $this->assertFalse($hasContextConnection);

            $redis->multi();
            $redis->set('id', uniqid());
            $redis->exec();

            $hasContextConnection = Context::has('redis.connection.default');
            $this->assertTrue($hasContextConnection);

            $connection = $method->invoke($redis, [$hasContextConnection]);
            $id = $connection->id;
        });

        $chan->pop();
        $factory = $ref->getProperty('factory');
        $factory = $factory->getValue($redis);
        $pool = $factory->getPool('default');
        $pool->flushAll();
        $chan->pop();
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
        } catch (Throwable $exception) {
            $this->assertSame('Get connection failed.', $exception->getMessage());
        }

        $this->assertSame(1, $pool->getConnectionsInChannel());
        $this->assertSame(1, $pool->getCurrentConnections());
    }

    public function testRedisClusterConstructor()
    {
        $ref = new ReflectionClass(RedisCluster::class);
        $method = $ref->getMethod('__construct');
        $names = [
            ['name', 'string'],
            ['seeds', 'array'],
            ['timeout', ['int', 'float']],
            ['read_timeout', ['int', 'float']],
            ['persistent', 'bool'],
            ['auth', 'mixed'],
            ['context', 'array'],
        ];
        foreach ($method->getParameters() as $parameter) {
            [$name, $type] = array_shift($names);
            $this->assertSame($name, $parameter->getName());
            if ($parameter->getName() === 'seeds') {
                $this->assertSame('array', $parameter->getType()->getName());
            } else {
                if (! $this->isOlderThan6) {
                    if (is_array($type)) {
                        foreach ($parameter->getType()->getTypes() as $namedType) {
                            $this->assertTrue(in_array($namedType->getName(), $type));
                        }
                    } else {
                        $this->assertSame($type, $parameter->getType()->getName());
                    }
                } else {
                    $this->assertNull($parameter->getType());
                }
            }
        }
    }

    public function testNewRedisCluster()
    {
        try {
            $container = $this->getContainer();
            $pool = new RedisPoolStub($container, 'cluster1');
            $container->shouldReceive('make')->once()->with(RedisPool::class, ['name' => 'cluster1'])->andReturn($pool);
            $factory = new PoolFactory($container);
            $redis = new RedisProxy($factory, 'cluster1');
            $redis->get('test');
            $this->assertTrue(false);
        } catch (Throwable $exception) {
            $this->assertInstanceOf(ConnectionException::class, $exception);
            $this->assertStringNotContainsString('RedisCluster::__construct() expects parameter', $exception->getMessage());
        }
    }

    public function testShuffleNodes()
    {
        $nodes = ['127.0.0.1:6379', '127.0.0.1:6378', '127.0.0.1:6377'];

        shuffle($nodes);

        $this->assertIsArray($nodes);
        $this->assertSame(3, count($nodes));
    }

    public function testRedisSentinelParams()
    {
        $rel = new ReflectionClass(RedisSentinel::class);
        $method = $rel->getMethod('__construct');
        $count = count($method->getParameters());

        if (! $this->isOlderThan6) {
            $this->assertSame(1, $count);
            $this->assertSame('options', $method->getParameters()[0]->getName());
        } else {
            if ($count === 6) {
                $this->markTestIncomplete('RedisSentinel don\'t support auth.');
            }
            $this->assertSame(7, $count);
        }
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
                'cluster1' => [
                    'timeout' => 1.0,
                    'auth' => null,
                    'cluster' => [
                        'enable' => true,
                        'name' => 'mycluster',
                        'seeds' => [],
                        'read_timeout' => 1.0,
                        'persistent' => false,
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
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();
        return $container;
    }
}
