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

namespace HyperfTest\Kafka\Stub;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Kafka\Constants\KafkaStrategy;
use Hyperf\Kafka\Producer;
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
            $dispatcher = Mockery::mock(EventDispatcherInterface::class);
            $dispatcher->shouldReceive('dispatch')->andReturnNull();
            return $dispatcher;
        });
        $container->shouldReceive('has')->andReturnUsing(function ($class) {
            return true;
        });

        $container->shouldReceive('get')->with(Producer::class)->andReturnUsing(function () {
            return Mockery::mock(Producer::class);
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
                    'client_id' => 'hyperf',
                    'max_write_attempts' => 3,
                    'bootstrap_servers' => '127.0.0.1:9092',
                    'acks' => 0,
                    'producer_id' => -1,
                    'producer_epoch' => -1,
                    'partition_leader_epoch' => -1,
                    'interval' => 0,
                    'session_timeout' => 60,
                    'rebalance_timeout' => 60,
                    'replica_id' => -1,
                    'rack_id' => '',
                    'group_retry' => 5,
                    'group_retry_sleep' => 1,
                    'group_heartbeat' => 3,
                    'offset_retry' => 5,
                    'auto_create_topic' => true,
                    'partition_assignment_strategy' => KafkaStrategy::RANGE_ASSIGNOR,
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
