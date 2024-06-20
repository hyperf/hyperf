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

namespace HyperfTest\Nsq\Stub;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Waiter;
use Hyperf\Di\Container;
use Hyperf\Engine\Contract\Socket\SocketFactoryInterface;
use Hyperf\Engine\Socket\SocketFactory;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Nsq\MessageBuilder;
use Hyperf\Nsq\Nsq;
use Hyperf\Nsq\Pool\NsqConnection;
use Hyperf\Nsq\Pool\NsqPool;
use Hyperf\Nsq\Pool\NsqPoolFactory;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
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

        $container->shouldReceive('make')->with(Waiter::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new Waiter(...$args);
        });
        $container->shouldReceive('has')->with(FormatterInterface::class)->andReturnFalse();
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->andReturn(null);
            $logger->shouldReceive('warning')->andReturn(null);
            return $logger;
        });
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturnUsing(function () {
            return Mockery::mock(EventDispatcherInterface::class);
        });
        $container->shouldReceive('has')->andReturnUsing(function ($class) {
            return true;
        });

        $container->shouldReceive('get')->with(NsqPoolFactory::class)->andReturnUsing(function () use ($container) {
            return new NsqPoolFactory($container);
        });

        $container->shouldReceive('get')->with(MessageBuilder::class)->andReturnUsing(function () {
            return new MessageBuilder();
        });

        $config = new Config([
            'nsq' => [
                'default' => [
                    'enable' => true,
                    'host' => '127.0.0.1',
                    'port' => 4150,
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 30.0,
                    ],
                    'nsqd' => [
                        'port' => 4151,
                        'options' => [
                        ],
                    ],
                ],
            ],
        ]);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

        $container->shouldReceive('make')->with(DemoConsumer::class, Mockery::any())->andReturnUsing(function () use ($container) {
            return new DemoConsumer($container);
        });

        $container->shouldReceive('make')->with(DisabledDemoConsumer::class, Mockery::any())->andReturnUsing(function () use ($container) {
            return new DisabledDemoConsumer($container);
        });

        $container->shouldReceive('make')->with(NsqPool::class, Mockery::any())->andReturnUsing(function ($_, $config) use ($container) {
            return new NsqPool($container, $config['name']);
        });

        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($_, $config) {
            return new PoolOption(...$config);
        });

        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($_, $config) {
            return new Channel(...$config);
        });

        $container->shouldReceive('make')->with(NsqConnection::class, Mockery::any())->andReturnUsing(function ($_, $config) {
            return new NsqConnection(...$config);
        });

        $container->shouldReceive('get')->with(SocketFactoryInterface::class)->andReturnUsing(function () {
            return new SocketFactory();
        });

        $container->shouldReceive('make')->with(Nsq::class, Mockery::any())->andReturn(Mockery::mock(Nsq::class));

        return $container;
    }
}
