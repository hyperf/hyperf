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
use HyperfTest\Redis\Stub\RedisPoolStub;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RedisConnectionTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testRedisConnectionConfig()
    {
        $pool = $this->getRedisPool();

        $config = $pool->get()->getConfig();

        $this->assertSame([
            'host' => 'redis',
            'port' => 16379,
            'auth' => 'redis',
            'db' => 0,
            'timeout' => 0.0,
            'options' => [],
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 30,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'max_idle_time' => 1,
            ],
        ], $config);
    }

    public function testRedisConnectionReconnect()
    {
        $pool = $this->getRedisPool();

        $connection = $pool->get()->getConnection();
        $this->assertSame(null, $connection->getDatabase());

        $connection->setDatabase(2);
        $connection->reconnect();
        $this->assertSame(2, $connection->getDatabase());

        $connection->release();
        $connection = $pool->get()->getConnection();
        $this->assertSame(null, $connection->getDatabase());
    }

    private function getRedisPool()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'redis' => [
                'default' => [
                    'host' => 'redis',
                    'auth' => 'redis',
                    'port' => 16379,
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 30,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 1,
                    ],
                ],
            ],
        ]));

        return new RedisPoolStub($container, 'default');
    }
}
