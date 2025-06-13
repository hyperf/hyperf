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

namespace HyperfTest\ConfigCenter\Listener;

use Hyperf\Config\Config;
use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\ConfigCenter\Listener\OnPipeMessageListener;
use Hyperf\ConfigCenter\PipeMessage;
use Hyperf\ConfigEtcd\EtcdDriver;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\Event\PipeMessage as UserProcessPipeMessage;
use HyperfTest\ConfigCenter\ContainerStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class OnPipeMessageListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateDriverInstanceAndReturnNull()
    {
        $factory = Mockery::mock(DriverFactory::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);

        $config = new Config([
            'config_center' => [
                'enable' => false,
                'driver' => 'test',
            ],
        ]);
        $listener = new OnPipeMessageListener($factory, $config, $logger);
        $listener->process(new UserProcessPipeMessage([]));

        $config = new Config([
            'config_center' => [
                'enable' => true,
                'driver' => '',
            ],
        ]);
        $listener = new OnPipeMessageListener($factory, $config, $logger);
        $listener->process(new UserProcessPipeMessage([]));

        $this->assertTrue(true);
    }

    public function testOnPipeMessage()
    {
        $pipeMessage = Mockery::mock(PipeMessage::class);
        $pipeMessage->shouldReceive('getData')->once();
        $config = new Config([
            'config_center' => [
                'enable' => true,
                'driver' => 'etcd',
                'drivers' => [
                    'etcd' => [
                        'driver' => EtcdDriver::class,
                    ],
                ],
            ],
        ]);
        ContainerStub::mockContainer($config);
        $factory = new DriverFactory($config);
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $listener = new OnPipeMessageListener($factory, $config, $logger);
        $listener->process(new UserProcessPipeMessage($pipeMessage));
        $this->assertTrue(true);
    }

    public function testOnPipeMessageWithoutPipeMessageInterface()
    {
        $config = new Config([
            'config_center' => [
                'enable' => true,
                'driver' => 'etcd',
                'drivers' => [
                    'etcd' => [
                        'driver' => EtcdDriver::class,
                    ],
                ],
            ],
        ]);
        ContainerStub::mockContainer($config);
        $factory = new DriverFactory($config);
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $listener = new OnPipeMessageListener($factory, $config, $logger);
        $listener->process(new UserProcessPipeMessage(null));
        $this->assertTrue(true);
    }
}
