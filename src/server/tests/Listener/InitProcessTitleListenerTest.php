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

namespace HyperfTest\Server\Listener;

use Hyperf\Config\Config;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\OnManagerStart;
use Hyperf\Framework\Event\OnStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Listener\InitProcessTitleListener;
use HyperfTest\Server\Stub\DemoProcess;
use HyperfTest\Server\Stub\InitProcessTitleListenerStub;
use HyperfTest\Server\Stub\InitProcessTitleListenerStub2;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class InitProcessTitleListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testInitProcessTitleListenerListen()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(Mockery::any())->andReturn(false);

        $listener = new InitProcessTitleListener($container);

        $this->assertSame([
            OnStart::class,
            OnManagerStart::class,
            AfterWorkerStart::class,
            BeforeProcessHandle::class,
        ], $listener->listen());
    }

    public function testProcessDefaultName()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(Mockery::any())->andReturn(false);

        $listener = new InitProcessTitleListenerStub($container);
        $process = new DemoProcess($container);

        $listener->process(new BeforeProcessHandle($process, 1));

        if (! $listener->isSupportedOS()) {
            $this->assertSame(null, Context::get('test.server.process.title'));
        } else {
            $this->assertSame('test.demo.1', Context::get('test.server.process.title'));
        }
    }

    public function testProcessName()
    {
        $name = 'hyperf-skeleton.' . uniqid();
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(ConfigInterface::class)->andReturn(true);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturn(false);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'app_name' => $name,
        ]));

        $listener = new InitProcessTitleListenerStub($container);
        $process = new DemoProcess($container);

        $listener->process(new BeforeProcessHandle($process, 0));

        if (! $listener->isSupportedOS()) {
            $this->assertSame(null, Context::get('test.server.process.title'));
        } else {
            $this->assertSame($name . '.test.demo.0', Context::get('test.server.process.title'));
        }
    }

    public function testUserDefinedDot()
    {
        $name = 'hyperf-skeleton.' . uniqid();
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(ConfigInterface::class)->andReturn(true);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturn(false);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'app_name' => $name,
        ]));

        $listener = new InitProcessTitleListenerStub2($container);
        $process = new DemoProcess($container);

        $listener->process(new BeforeProcessHandle($process, 0));

        if (! $listener->isSupportedOS()) {
            $this->assertSame(null, Context::get('test.server.process.title'));
        } else {
            $this->assertSame($name . '#test.demo#0', Context::get('test.server.process.title'));
        }
    }
}
