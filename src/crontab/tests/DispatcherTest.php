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

namespace HyperfTest\Crontab;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Crontab\Process\CrontabDispatcherProcess;
use Hyperf\Crontab\Scheduler;
use Hyperf\Crontab\Strategy\StrategyInterface;
use Hyperf\Di\Container;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DispatcherTest extends TestCase
{
    public function mockContainer()
    {
        $container = Mockery::mock(Container::class);

        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(LoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(Mockery::mock(ConfigInterface::class));
        $container->shouldReceive('get')->with(Scheduler::class)->andReturn(Mockery::mock(Scheduler::class));
        $container->shouldReceive('get')->with(StrategyInterface::class)->andReturn(Mockery::mock(StrategyInterface::class));
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(Mockery::mock(StdoutLoggerInterface::class));
        ApplicationContext::setContainer($container);
    }

    public function testGetSchedules()
    {
        $this->mockContainer();
        $crontabDispatcherProcess = new CrontabDispatcherProcess(ApplicationContext::getContainer());
        $this->assertEquals(59.432, $crontabDispatcherProcess->getInterval(0, 0.568));
        $this->assertEquals(59.431, $crontabDispatcherProcess->getInterval(0, 0.5686));
        $this->assertEquals(60, $crontabDispatcherProcess->getInterval(0, 0));
        $this->assertEquals(2, $crontabDispatcherProcess->getInterval(58, 0));
        $this->assertEquals(1.001, $crontabDispatcherProcess->getInterval(58, 0.999));
        $this->assertEquals(1.724, $crontabDispatcherProcess->getInterval(58, 0.27644200));
    }
}
