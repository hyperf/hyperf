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

namespace HyperfTest\Cases;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Engine\Channel;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use Hyperf\Process\Event\PipeMessage;
use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Hyperf\ReactiveX\IpcMessageWrapper;
use Hyperf\ReactiveX\IpcSubject;
use Hyperf\ReactiveX\RxSwoole;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use Rx\Notification\OnNextNotification;
use Rx\Scheduler\EventLoopScheduler;
use Rx\SchedulerInterface;
use Rx\Subject\Subject;
use Swoole\Runtime;

use function Hyperf\Coroutine\go;
use function Hyperf\Support\swoole_hook_flags;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class IpcSubjectTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Runtime::enableCoroutine(swoole_hook_flags());
    }

    protected function setUp(): void
    {
        $container = new Container(new DefinitionSource([]));
        $container->define(SchedulerInterface::class, EventLoopScheduler::class);
        ApplicationContext::setContainer($container);
        RxSwoole::init();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testOnNext()
    {
        $result = new Channel(1);
        go(function () use ($result) {
            $broadcaster = Mockery::mock(BroadcasterInterface::class);
            $event = new IpcMessageWrapper(0, new OnNextNotification(42));
            $broadcaster->shouldReceive('broadcast')->with(
                Mockery::on(fn ($argument) => $event == $argument)
            )->once();
            $container = Mockery::mock(Container::class);
            $container->shouldReceive('get')->with(ListenerProviderInterface::class)
                ->andReturn(new ListenerProvider());
            ApplicationContext::setContainer($container);
            $subject = new IpcSubject(new Subject(), $broadcaster, 0);
            $subject->subscribe(
                function ($x) use ($result) {
                    $result->push($x);
                }
            );
            $subject->onNext(42);
        });
        $this->assertEquals(42, $result->pop());
    }

    public function testOnMessage()
    {
        $container = ApplicationContext::getContainer();
        $broadcaster = Mockery::mock(BroadcasterInterface::class);
        $container->set(BroadcasterInterface::class, $broadcaster);
        $event = new IpcMessageWrapper(0, new OnNextNotification(42));
        $provider = new ListenerProvider();
        $container->set(ListenerProviderInterface::class, $provider);
        ApplicationContext::setContainer($container);
        $subject = new IpcSubject(
            new Subject(),
            $broadcaster,
            0
        );
        $result = new Channel(1);
        $subject->subscribe(
            function ($x) use ($result) {
                $result->push($x);
            }
        );
        $dispatcher = new EventDispatcher($provider);
        $dispatcher->dispatch(new PipeMessage($event));
        $this->assertEquals(42, $result->pop());
    }
}
