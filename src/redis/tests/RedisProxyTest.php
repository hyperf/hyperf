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

    public function testZSetAddAnd()
    {
        $key = 'test:zset:add:remove';
        $redis = $this->getRedis();
        $redis->del($key);

        $redis->zAdd($key, microtime(true) * 1000 + 2, 'test');
        usleep(1000);
        $res = $redis->zRangeByScore($key, '0', (string) (microtime(true) * 1000));
        $this->assertEmpty($res);

        // $redis->zAdd($key, microtime(true) * 1000 + 1, 'test');
        // usleep(500);
        // $res = $redis->zRangeByScore($key, '0', (string) (microtime(true) * 1000));
        // $this->assertEmpty($res);
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
     * Test that pipeline and transaction callbacks properly release connections
     * immediately, preventing pool exhaustion in concurrent scenarios.
     */
    public function testConcurrentPipelineCallbacksWithLimitedConnectionPool()
    {
        // Create Redis instance with very limited connection pool
        $redis = $this->getRedisWithLimitedPool(3); // Only 3 max connections
        
        $concurrentOperations = 20; // Much more than max_connections
        $channels = [];
        
        // Create channels for coordination between coroutines
        for ($i = 0; $i < $concurrentOperations; $i++) {
            $channels[$i] = new Chan(1);
        }

        // Start many concurrent coroutines using pipeline with callbacks
        for ($i = 0; $i < $concurrentOperations; $i++) {
            go(function() use ($redis, $channels, $i) {
                try {
                    $key = "concurrent_pipeline_test_{$i}";
                    
                    // Use pipeline with callback - should release connection immediately after callback
                    $results = $redis->pipeline(function (\Redis $pipe) use ($key) {
                        $pipe->set($key, "value_{$key}");
                        $pipe->incr("{$key}_counter");
                        $pipe->get($key);
                        $pipe->get("{$key}_counter");
                    });
                    
                    // Verify results to ensure operations actually worked
                    $this->assertCount(4, $results);
                    $this->assertTrue($results[0]); // SET command result
                    $this->assertSame(1, $results[1]); // INCR result  
                    $this->assertSame("value_{$key}", $results[2]); // GET result
                    $this->assertSame(1, $results[3]); // Counter value
                    
                    $channels[$i]->push(['success' => true, 'operation' => 'pipeline']);
                } catch (\Throwable $e) {
                    $channels[$i]->push(['success' => false, 'error' => $e->getMessage()]);
                }
            });
        }

        // Wait for all operations to complete and verify success
        $successCount = 0;
        for ($i = 0; $i < $concurrentOperations; $i++) {
            $result = $channels[$i]->pop(10.0); // 10 second timeout
            $this->assertNotFalse($result, "Operation {$i} timed out - possible connection pool exhaustion");
            
            if ($result['success']) {
                $successCount++;
            } else {
                $this->fail("Concurrent operation {$i} failed: " . $result['error']);
            }
        }
        
        $this->assertSame($concurrentOperations, $successCount, 
            "All {$concurrentOperations} concurrent pipeline operations should succeed with only 3 max connections");
        
        // Clean up test data
        for ($i = 0; $i < $concurrentOperations; $i++) {
            $redis->del("concurrent_pipeline_test_{$i}");
            $redis->del("concurrent_pipeline_test_{$i}_counter");
        }
    }

    /**
     * Test that transaction callbacks also properly release connections immediately.
     */
    public function testConcurrentTransactionCallbacksWithLimitedConnectionPool()
    {
        // Create Redis instance with very limited connection pool  
        $redis = $this->getRedisWithLimitedPool(3); // Only 3 max connections
        
        $concurrentOperations = 15; // More than max_connections
        $channels = [];
        
        // Create channels for coordination
        for ($i = 0; $i < $concurrentOperations; $i++) {
            $channels[$i] = new Chan(1);
        }

        // Start concurrent coroutines using transaction with callbacks
        for ($i = 0; $i < $concurrentOperations; $i++) {
            go(function() use ($redis, $channels, $i) {
                try {
                    $key = "concurrent_transaction_test_{$i}";
                    
                    // Use transaction with callback - should release connection immediately after callback
                    $results = $redis->transaction(function (\Redis $transaction) use ($key) {
                        $transaction->set($key, "tx_value_{$key}");
                        $transaction->incr("{$key}_counter");
                        $transaction->get($key);
                    });
                    
                    // Verify transaction results
                    $this->assertCount(3, $results);
                    $this->assertTrue($results[0]); // SET result
                    $this->assertSame(1, $results[1]); // INCR result
                    $this->assertSame("tx_value_{$key}", $results[2]); // GET result
                    
                    $channels[$i]->push(['success' => true, 'operation' => 'transaction']);
                } catch (\Throwable $e) {
                    $channels[$i]->push(['success' => false, 'error' => $e->getMessage()]);
                }
            });
        }

        // Collect and verify all results
        $successCount = 0;
        for ($i = 0; $i < $concurrentOperations; $i++) {
            $result = $channels[$i]->pop(10.0); // 10 second timeout
            $this->assertNotFalse($result, "Transaction operation {$i} timed out - possible connection pool exhaustion");
            
            if ($result['success']) {
                $successCount++;
            } else {
                $this->fail("Concurrent transaction {$i} failed: " . $result['error']);
            }
        }
        
        $this->assertSame($concurrentOperations, $successCount,
            "All {$concurrentOperations} concurrent transaction operations should succeed with only 3 max connections");
        
        // Clean up
        for ($i = 0; $i < $concurrentOperations; $i++) {
            $redis->del("concurrent_transaction_test_{$i}");
            $redis->del("concurrent_transaction_test_{$i}_counter");
        }
    }

    /**
     * Helper method to create Redis instance with custom limited connection pool.
     */
    private function getRedisWithLimitedPool(int $maxConnections)
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
                    'options' => [],
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => $maxConnections, // Limited pool size
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
        $container->shouldReceive('make')->with(Channel::class, ['size' => $maxConnections])->andReturn(new Channel($maxConnections));
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        ApplicationContext::setContainer($container);

        $factory = new PoolFactory($container);
        return new Redis($factory);
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
