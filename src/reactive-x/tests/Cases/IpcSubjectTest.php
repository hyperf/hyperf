<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Cases;

use Hyperf\Di\Container;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use Hyperf\Process\Event\PipeMessage;
use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Hyperf\ReactiveX\IpcMessageWrapper;
use Hyperf\ReactiveX\IpcSubject;
use Hyperf\ReactiveX\RxSwoole;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use Rx\Notification\OnNextNotification;
use Rx\Subject\Subject;
use Swoole\Coroutine\Channel;

/**
 * @internal
 * @coversNothing
 */
class IpcSubjectTest extends TestCase
{
    public function setUp()
    {
        RxSwoole::init();
    }

    public function tearDown()
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
                Mockery::on(function ($argument) use ($event) {
                    return $event == $argument;
                })
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
        $broadcaster = Mockery::mock(BroadcasterInterface::class);
        $event = new IpcMessageWrapper(0, new OnNextNotification(42));
        $provider = new ListenerProvider();
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ListenerProviderInterface::class)
            ->andReturn($provider);
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
