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
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Engine\Channel as Chan;
use Hyperf\Pool\Channel;
use Hyperf\Pool\LowFrequencyInterface;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RedisCluster;

use function Hyperf\Coroutine\go;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RedisProxyTest extends TestCase
{
    protected function tearDown(): void
    {
        $redis = $this->getRedis();
        $redis->flushDB();

        Mockery::close();
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

    public function testHyperLogLog()
    {
        $redis = $this->getRedis();
        $res = $redis->pfAdd('test:hyperloglog', ['123', 'fff']);
        $this->assertSame(1, $res);
        $res = $redis->pfAdd('test:hyperloglog', ['123']);
        $this->assertSame(0, $res);
        $this->assertSame(2, $redis->pfCount('test:hyperloglog'));
        $redis->pfAdd('test:hyperloglog2', [1234]);
        $redis->pfMerge('test:hyperloglog2', ['test:hyperloglog']);
        $this->assertSame(3, $redis->pfCount('test:hyperloglog2'));
        $this->assertFalse($redis->pfAdd('test:hyperloglog3', []));
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

    public function testRedisScan()
    {
        $redis = $this->getRedis();
        $origin = ['scan:1', 'scan:2', 'scan:3', 'scan:4'];
        foreach ($origin as $value) {
            $redis->set($value, '1');
        }

        $it = null;
        $result = [];
        while (false !== $res = $redis->scan($it, 'scan:*', 2)) {
            $result = array_merge($result, $res);
        }

        sort($result);

        $this->assertEquals($origin, $result);
        $this->assertSame(0, $it);
    }

    public function testRedisHScan()
    {
        $redis = $this->getRedis();
        $origin = ['scan:1', 'scan:2', 'scan:3', 'scan:4'];
        foreach ($origin as $value) {
            $redis->hSet('scaner', $value, '1');
        }

        $it = null;
        $result = [];
        while (false !== $res = $redis->hScan('scaner', $it, 'scan:*', 2)) {
            $result = array_merge($result, array_keys($res));
        }

        sort($result);

        $this->assertEquals($origin, $result);
        $this->assertSame(0, $it);
    }

    public function testPipeline()
    {
        $pipe = $this->getRedis()->pipeline();
        $this->assertInstanceOf(\Redis::class, $pipe);

        $key = 'pipeline:' . uniqid();

        $this->getRedis()->pipeline(function (\Redis $pipe) use ($key) {
            $pipe->incr($key);
            $pipe->incr($key);
            $pipe->incr($key);
        });

        $this->assertEquals(3, $this->getRedis()->get($key));

        $this->getRedis()->del($key);
    }

    public function testTransaction()
    {
        $transaction = $this->getRedis()->transaction();
        $this->assertInstanceOf(\Redis::class, $transaction);

        $key = 'transaction:' . uniqid();

        $this->getRedis()->transaction(function (\Redis|RedisCluster $transaction) use ($key) {
            $transaction->incr($key);
            $transaction->incr($key);
            $transaction->incr($key);
        });

        $this->assertEquals(3, $this->getRedis()->get($key));

        $this->getRedis()->del($key);
    }

    public function testRedisPipeline()
    {
        $redis = $this->getRedis();

        $redis->rPush('pipeline:list', 'A');
        $redis->rPush('pipeline:list', 'B');
        $redis->rPush('pipeline:list', 'C');
        $redis->rPush('pipeline:list', 'D');
        $redis->rPush('pipeline:list', 'E');

        $chan = new Chan(1);
        $chan2 = new Chan(1);
        go(static function () use ($redis, $chan) {
            $redis->pipeline();
            usleep(2000);
            $redis->lRange('pipeline:list', 0, 1);
            $redis->lTrim('pipeline:list', 2, -1);
            usleep(1000);
            $chan->push($redis->exec());
        });

        go(static function () use ($redis, $chan2) {
            $redis->pipeline();
            usleep(1000);
            $redis->lRange('pipeline:list', 0, 1);
            $redis->lTrim('pipeline:list', 2, -1);
            usleep(20000);
            $chan2->push($redis->exec());
        });

        $this->assertSame([['A', 'B'], true], $chan->pop());
        $this->assertSame([['C', 'D'], true], $chan2->pop());
    }

    /**
     * @param mixed $options
     * @return \Redis|Redis
     */
    private function getRedis($options = [])
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'redis' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'auth' => null,
                    'port' => 6379,
                    'db' => 0,
                    'options' => $options,
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
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        ApplicationContext::setContainer($container);

        $factory = new PoolFactory($container);

        return new Redis($factory);
    }
}
