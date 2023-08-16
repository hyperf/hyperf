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
namespace HyperfTest\ConfigCenter\Process;

use Hyperf\Config\Config;
use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\ConfigCenter\Mode;
use Hyperf\ConfigCenter\Process\ConfigFetcherProcess;
use Hyperf\ConfigEtcd;
use Hyperf\ConfigEtcd\EtcdDriver;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Di\Container;
use Hyperf\Process\ProcessManager;
use HyperfTest\ConfigCenter\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;

/**
 * @internal
 * @coversNothing
 */
class ConfigFetcherProcessTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        CoordinatorManager::clear(Constants::WORKER_EXIT);
    }

    public function testIsEnable()
    {
        $config = new Config([
            'config_center' => [
                'enable' => false,
                'mode' => Mode::PROCESS,
            ],
        ]);
        $container = ContainerStub::mockContainer($config);
        $process = new ConfigFetcherProcess($container);
        $this->assertFalse($process->isEnable(Mockery::mock(Server::class)));

        $config = new Config([
            'config_center' => [
                'enable' => true,
                'mode' => Mode::COROUTINE,
            ],
        ]);
        $container = ContainerStub::mockContainer($config);
        $process = new ConfigFetcherProcess($container);
        $this->assertFalse($process->isEnable(Mockery::mock(Server::class)));

        $config = new Config([
            'config_center' => [
                'enable' => true,
                'mode' => Mode::PROCESS,
            ],
        ]);
        $container = ContainerStub::mockContainer($config);
        $process = new ConfigFetcherProcess($container);
        $this->assertTrue($process->isEnable(Mockery::mock(Server::class)));
    }

    public function testCreateDriver()
    {
        $config = new Config([
            'config_center' => [
                'enable' => true,
                'mode' => Mode::PROCESS,
                'driver' => 'etcd',
                'drivers' => [
                    'etcd' => [
                        'driver' => EtcdDriver::class,
                    ],
                ],
            ],
        ]);
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->andReturnFalse();
            return $logger;
        });
        $container->shouldReceive('get')->with(DriverFactory::class)->andReturn(new DriverFactory($config));
        $container->shouldReceive('make')->with(ConfigEtcd\EtcdDriver::class, Mockery::any())->andReturnUsing(function () {
            $driver = Mockery::mock(ConfigEtcd\EtcdDriver::class);
            $driver->shouldReceive('setServer')->once()->andReturnSelf();
            $driver->shouldReceive('createMessageFetcherLoop')->once()->andReturnNull();
            return $driver;
        });

        $process = new ConfigFetcherProcess($container);
        ProcessManager::setRunning(false);
        $process->handle();
        $this->assertTrue(true);
    }
}
