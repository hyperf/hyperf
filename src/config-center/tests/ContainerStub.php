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
namespace HyperfTest\ConfigCenter;

use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use Psr\Container\ContainerInterface;

class ContainerStub
{
    public static function mockContainer(ConfigInterface $config)
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->andReturnFalse();
            return $logger;
        });
        $container->shouldReceive('get')->with(DriverFactory::class)->andReturn(new DriverFactory($config));

        return $container;
    }
}
