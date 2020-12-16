<?php

namespace HyperfTest\Kafka\Stub;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Kafka\Producer;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ContainerStub
{
    /**
     * @return ContainerInterface
     */
    public static function getContainer()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->andReturn(null);
            return $logger;
        });
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturnUsing(function () {
            return Mockery::mock(EventDispatcherInterface::class);
        });
        $container->shouldReceive('has')->andReturnUsing(function ($class) {
            return true;
        });

        $container->shouldReceive('get')->with(Producer::class)->andReturnUsing(function () use ($container) {
            return  Mockery::mock(Producer::class);
        });

        $container->shouldReceive('make')->with(DemoConsumer::class, Mockery::any())->andReturnUsing(function () use ($container) {
            return new DemoConsumer($container);
        });

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'kafka' => [
                'default' => [
                    'connect_timeout' => -1,
                    'send_timeout' => -1,
                    'recv_timeout' => -1,
                    'client_id' => '',
                    'max_write_attempts' => 3,
                    'brokers' => [
                        '127.0.0.1:9092',
                    ],
                    'bootstrap_server' => '127.0.0.1:9092',
                    'update_brokers' => true,
                    'acks' => 0,
                    'producer_id' => -1,
                    'producer_epoch' => -1,
                    'partition_leader_epoch' => -1,
                    'interval' => 0,
                    'session_timeout' => 60,
                    'rebalance_timeout' => 60,
                    'partitions' => [0],
                    'replica_id' => -1,
                    'rack_id' => '',
                    'is_auto_create_topic' => true,
                    'num_partitions' => 1,
                    'replication_factor' => 3,
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60.0,
                    ],
                ],
            ],
        ]));

        return $container;
    }
}
