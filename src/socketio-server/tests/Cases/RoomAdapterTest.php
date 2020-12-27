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
namespace HyperfTest\SocketIOServer\Cases;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Framework\Logger\StdoutLogger;
use Hyperf\Pool\Channel;
use Hyperf\Pool\LowFrequencyInterface;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Hyperf\SocketIOServer\NamespaceInterface;
use Hyperf\SocketIOServer\Room\MemoryAdapter;
use Hyperf\SocketIOServer\Room\RedisAdapter;
use Hyperf\SocketIOServer\SidProvider\LocalSidProvider;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Sender;
use Mix\Redis\Subscribe\Subscriber;
use Mockery;

/**
 * @internal
 * @coversNothing
 */
class RoomAdapterTest extends AbstractTestCase
{
    public function testMemoryAdapter()
    {
        $sidProvider = new LocalSidProvider();
        $server = Mockery::Mock(Sender::class);
        $server->shouldReceive('push')->twice();
        $room = new MemoryAdapter($server, $sidProvider);
        $room->add('42', 'universe', '42');
        $room->add('43', 'universe', '43');
        $this->assertContains('universe', $room->clientRooms('43'));
        $this->assertContains('43', $room->clientRooms('43'));
        $this->assertContains('42', $room->clientRooms('42'));
        $this->assertContains('universe', $room->clientRooms('42'));
        $this->assertContains('42', $room->clients('universe'));
        $this->assertContains('43', $room->clients('universe'));
        $room->broadcast('', ['rooms' => ['universe']]);
        $room->del('42', 'universe');
        $this->assertContains('42', $room->clientRooms('42'));
        $this->assertNotContains('universe', $room->clientRooms('42'));
        $this->assertNotEmpty($room->clientRooms('42'));
        $room->del('43');
        $this->assertNotContains('43', $room->clientRooms('43'));
        $this->assertNotContains('universe', $room->clientRooms('43'));
        $this->assertEmpty($room->clientRooms('43'));
        $room->broadcast('', ['rooms' => ['universe']]);
    }

    public function testDelFromEmptyRoom()
    {
        $sidProvider = new LocalSidProvider();
        $server = Mockery::Mock(Sender::class);
        $room = new MemoryAdapter($server, $sidProvider);
        $room->del('111');

        $nsp = Mockery::Mock(NamespaceInterface::class);
        $nsp->shouldReceive('getNamespace')->andReturn('test');
        $redis = $this->getRedis();
        $server = Mockery::Mock(Sender::class);
        $sidProvider = new LocalSidProvider();
        $room = new RedisAdapter($redis, $server, $nsp, $sidProvider);
        $room->del('111');

        $this->assertTrue(true);
    }

    public function testRedisAdapter()
    {
        $nsp = Mockery::Mock(NamespaceInterface::class);
        $nsp->shouldReceive('getNamespace')->andReturn('test');
        $redis = $this->getRedis();
        $server = Mockery::Mock(Sender::class);
        $server->shouldReceive('push')->twice();
        $sidProvider = new LocalSidProvider();
        $room = new RedisAdapter($redis, $server, $nsp, $sidProvider);
        $room->add('42', 'universe', '42');
        $room->add('43', 'universe', '43');
        $this->assertContains('universe', $room->clientRooms('43'));
        $this->assertContains('43', $room->clientRooms('43'));
        $this->assertContains('42', $room->clientRooms('42'));
        $this->assertContains('universe', $room->clientRooms('42'));
        $this->assertContains('42', $room->clients('universe'));
        $this->assertContains('43', $room->clients('universe'));
        $room->broadcast('', ['rooms' => ['universe'], 'flag' => ['local' => true]]);
        $room->del('42', 'universe');
        $this->assertContains('42', $room->clientRooms('42'));
        $this->assertNotContains('universe', $room->clientRooms('42'));
        $this->assertNotEmpty($room->clientRooms('42'));
        $room->del('43');
        $this->assertNotContains('43', $room->clientRooms('43'));
        $this->assertNotContains('universe', $room->clientRooms('43'));
        $this->assertEmpty($room->clientRooms('43'));
        $room->broadcast('', ['rooms' => ['universe'], 'flag' => ['local' => true]]);
        $room->cleanUp();
        $this->assertNotContains('42', $room->clientRooms('42'));

        // Test empty room
        try {
            $room->del('non-exist');
        } catch (\Throwable $t) {
            $this->assertTrue(false);
        }

        // Test Ephemeral
        $room->setTtl(1);
        $room->add('expired', 'foo');
        usleep(1000);
        $room->cleanUpExpiredOnce();
        $this->assertNotContains('expired', $room->clients('foo'));

        $room->setTtl(100000);
        $room->add('not_expired', 'foo');
        $room->cleanUpExpiredOnce();
        $this->assertContains('not_expired', $room->clients('foo'));

        $room->setTtl(1);
        $room->add('renewed', 'foo');
        $room->renew('renewed');
        usleep(500);
        $room->cleanUpExpiredOnce();
        $this->assertContains('renewed', $room->clients('foo'));

        $room->renew('not_exist');
        $room->cleanUpExpiredOnce();
        $this->assertNotContains('not_exist', $room->clients('foo'));
    }

    private function getRedis($options = [])
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'redis' => [
                'default' => [
                    'host' => 'localhost',
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
        $subscriber = Mockery::mock(Subscriber::class);
        $subscriber->shouldReceive('subscribe')->withAnyArgs()->andReturn();
        $subscriber->shouldReceive('channel')->andReturn(false);
        $container->shouldReceive('has')->andReturn(false);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(new StdoutLogger(new Config([])));
        $container->shouldReceive('get')->with(Subscriber::class)->andReturn($subscriber);
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn($frequency);
        $container->shouldReceive('make')->with(RedisPool::class, ['name' => 'default'])->andReturn($pool);
        $container->shouldReceive('make')->with(Channel::class, ['size' => 30])->andReturn(new Channel(30));
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        ApplicationContext::setContainer($container);
        $factory = new PoolFactory($container);
        $mock = Mockery::mock(RedisFactory::class);
        $mock->shouldReceive('get')->andReturn(new Redis($factory));
        return $mock;
    }
}
