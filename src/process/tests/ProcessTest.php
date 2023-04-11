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
namespace HyperfTest\Process;

use Hyperf\Context\ApplicationContext;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use HyperfTest\Process\Stub\FooProcess;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class ProcessTest extends TestCase
{
    public static $dispatched = [];

    protected function tearDown(): void
    {
        Mockery::close();
        self::$dispatched = [];
    }

    /**
     * @group NonCoroutine
     */
    public function testEventWhenThrowExceptionInProcess()
    {
        $container = $this->getContainer();
        $process = new FooProcess($container);
        $process->bind($this->getServer());

        $this->assertInstanceOf(BeforeProcessHandle::class, self::$dispatched[0]);
        $this->assertInstanceOf(AfterProcessHandle::class, self::$dispatched[1]);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturnUsing(function () {
            $dispatcher = Mockery::mock(EventDispatcherInterface::class);
            $dispatcher->shouldReceive('dispatch')->withAnyArgs()->andReturnUsing(function ($event) {
                self::$dispatched[] = $event;
            });
            return $dispatcher;
        });

        return $container;
    }

    protected function getServer()
    {
        $server = Mockery::mock(\Swoole\Server::class);
        $server->shouldReceive('addProcess')->withAnyArgs()->andReturnUsing(function ($process) {
            $ref = new ReflectionClass($process);
            $property = $ref->getProperty('callback');
            $property->setAccessible(true);
            $callback = $property->getValue($process);
            $callback($process);
            return 1;
        });
        return $server;
    }
}
