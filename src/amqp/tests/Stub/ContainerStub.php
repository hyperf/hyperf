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

namespace HyperfTest\Amqp\Stub;

use Hyperf\Amqp\ConnectionFactory;
use Hyperf\Amqp\Consumer;
use Hyperf\Amqp\IO\IOFactory;
use Hyperf\Amqp\Pool\PoolFactory;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\ExceptionHandler\Formatter\DefaultFormatter;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
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
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () {
            return new Config([
                'amqp' => [
                    'default' => [
                        'concurrent' => [
                            'limit' => 10,
                        ],
                    ],
                    'co' => [
                        'concurrent' => [
                            'limit' => 5,
                        ],
                    ],
                ],
            ]);
        });
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnTrue();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(ConnectionFactory::class)->andReturn(new ConnectionFactory($container));
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->andReturn(null);
            $logger->shouldReceive('log')->andReturn(null);
            $logger->shouldReceive('error')->andReturn(null);
            return $logger;
        });
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturnUsing(function () {
            return Mockery::mock(EventDispatcherInterface::class);
        });
        $container->shouldReceive('has')->andReturnUsing(function ($class) {
            return true;
        });
        $container->shouldReceive('get')->with(Consumer::class)->andReturnUsing(function () use ($container) {
            return new Consumer($container, $container->get(ConnectionFactory::class), $container->get(StdoutLoggerInterface::class));
        });
        $container->shouldReceive('get')->with(FormatterInterface::class)->andReturn(new DefaultFormatter());
        $container->shouldReceive('get')->with(IOFactory::class)->andReturn(new IOFactory());
        return $container;
    }

    public static function getHyperfContainer()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(PoolFactory::class)->andReturn(new PoolFactory($container));
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(
            Mockery::mock(EventDispatcherInterface::class)
        );
        $container->shouldReceive('has')->andReturn(true);
        return $container;
    }
}
