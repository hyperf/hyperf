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
namespace HyperfTest\Watcher\Stub;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Mockery;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;

class ContainerStub
{
    public static function getContainer(string $driver): MockInterface|ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () use ($driver) {
            return new Config([
                'watcher' => [
                    'driver' => $driver,
                    'bin' => 'php',
                    'watch' => [
                        'dir' => ['/tmp'],
                        'file' => ['.env'],
                        'scan_interval' => 1,
                    ],
                ],
            ]);
        });
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->andReturn(null);
            $logger->shouldReceive('log')->andReturn(null);
            return $logger;
        });
        return $container;
    }
}
