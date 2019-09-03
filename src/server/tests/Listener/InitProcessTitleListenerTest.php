<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Server\Listener;

use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\OnManagerStart;
use Hyperf\Framework\Event\OnStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Listener\InitProcessTitleListener;
use Hyperf\Utils\Context;
use HyperfTest\Server\Stub\DemoProcess;
use HyperfTest\Server\Stub\InitProcessTitleListenerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class InitProcessTitleListenerTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testInitProcessTitleListenerListen()
    {
        $container = Mockery::mock(ContainerInterface::class);
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

        $this->assertSame('Hyperf.test.demo.1', Context::get('test.server.process.title'));
    }

    public function testProcessName()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(Mockery::any())->andReturn(false);

        $listener = new InitProcessTitleListenerStub($container);
        $process = new DemoProcess($container);

        $listener->process(new BeforeProcessHandle($process, 1));

        $this->assertSame('Hyperf.test.demo.1', Context::get('test.server.process.title'));
    }
}
