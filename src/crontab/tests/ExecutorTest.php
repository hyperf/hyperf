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

use Carbon\Carbon;
use Hyperf\Command\Command;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Listener\OnPipeMessageListener;
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Crontab\PipeMessage;
use Hyperf\Crontab\Strategy\Executor;
use Hyperf\Engine\Channel;
use Hyperf\Framework\Event\OnPipeMessage;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Http\Server;
use Symfony\Component\Console\Application;

/**
 * @internal
 * @coversNothing
 */
class ExecutorTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function tearDown(): void
    {
        Mockery::close();
    }

    #[Group('NonCoroutine')]
    public function testExecute22()
    {
        $data = file_get_contents(__DIR__ . '/Stub/cron22.data');
        $server = Mockery::mock(Server::class);
        $event = new OnPipeMessage($server, 1, new PipeMessage(...unserialize($data)));
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(LoggerInterface::class)->once()->andReturnFalse();
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->twice()->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->once()->andReturnFalse();
        $container->shouldReceive('get')->with(Executor::class)->once()->andReturn(new Executor($container));
        $listener = new OnPipeMessageListener($container);
        $listener->process($event);
    }

    public function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(LoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();
        return $container;
    }

    public function testExecuteCommand()
    {
        $chan = new Channel(1);
        $command = new class($chan) extends Command {
            private $chan;

            public function __construct(Channel $chan)
            {
                parent::__construct('demo');
                $this->chan = $chan;
            }

            public function handle()
            {
                $this->chan->push('ok');
            }
        };

        $container = $this->getContainer();
        $app = new Application();
        $app->add($command);
        $container->shouldReceive('get')->with(ApplicationInterface::class)->andReturn($app);

        $crontab = Mockery::mock(Crontab::class);
        $crontab->shouldReceive('getExecuteTime')->andReturn(Carbon::now());
        $crontab->shouldReceive('getType')->andReturn('command');
        $crontab->shouldReceive('isSingleton')->andReturnFalse();
        $crontab->shouldReceive('isOnOneServer')->andReturnFalse();
        $crontab->shouldReceive('getCallback')->andReturn([
            'command' => 'demo',
        ]);
        (new Executor($container))->execute(
            $crontab
        );
        $this->assertEquals('ok', $chan->pop());
    }

    public function testExecuteCommandDisableEventDispatcher()
    {
        $this->assertTrue($this->executeCommandOption(['command' => 'demo']));
        $this->assertFalse($this->executeCommandOption([
            'command' => 'demo',
            '--disable-event-dispatcher' => false,
        ]));
    }

    public function testExecuteClosure()
    {
        $container = $this->getContainer();

        $chan = new Channel(1);
        $crontab = Mockery::mock(Crontab::class);
        $crontab->shouldReceive('getExecuteTime')->andReturn(Carbon::now());
        $crontab->shouldReceive('getType')->andReturn('closure');
        $crontab->shouldReceive('isSingleton')->andReturnFalse();
        $crontab->shouldReceive('isOnOneServer')->andReturnFalse();
        $crontab->shouldReceive('getCallback')->andReturn(function () use ($chan) {
            $chan->push('closure');
        });
        (new Executor($container))->execute(
            $crontab
        );
        $this->assertEquals('closure', $chan->pop());
    }

    private function executeCommandOption(array $callback): bool
    {
        $chan = new Channel(1);
        $command = new class($chan) extends Command {
            private $chan;

            public function __construct(Channel $chan)
            {
                parent::__construct('demo');
                $this->chan = $chan;
            }

            public function handle()
            {
                $this->chan->push($this->input->getOption('disable-event-dispatcher'));
            }
        };

        $container = $this->getContainer();
        $app = new Application();
        $app->add($command);
        $container->shouldReceive('get')->with(ApplicationInterface::class)->andReturn($app);

        $crontab = Mockery::mock(Crontab::class);
        $crontab->shouldReceive('getExecuteTime')->andReturn(Carbon::now());
        $crontab->shouldReceive('getType')->andReturn('command');
        $crontab->shouldReceive('isSingleton')->andReturnFalse();
        $crontab->shouldReceive('isOnOneServer')->andReturnFalse();
        $crontab->shouldReceive('getCallback')->andReturn($callback);
        $crontab->shouldReceive('complete')->andReturnNull();
        (new Executor($container))->execute($crontab);

        return $chan->pop();
    }

    // public function testDiffInRealSeconds()
    // {
    //     $now = Carbon::now();
    //     $res = $now->clone()->addSeconds(30)->diffInRealSeconds();
    //     $this->assertSame(30, $res);

    //     $now = Carbon::now();
    //     $res = $now->clone()->addSeconds(35)->diffInRealSeconds(new Carbon());
    //     $this->assertSame(35, $res);

    //     $now = Carbon::now();
    //     $res = $now->clone()->addSeconds(-20)->diffInRealSeconds(new Carbon());
    //     $this->assertSame(20, $res);

    //     $now = Carbon::now();
    //     $res = $now->clone()->addSeconds(-20)->diffInRealSeconds(new Carbon(), false);
    //     $this->assertSame(20, $res);

    //     $now = Carbon::now();
    //     $res = $now->diffInRealSeconds($now->clone()->addSeconds(30), false);
    //     $this->assertSame(30, $res);

    //     $now = Carbon::now();
    //     $res = $now->diffInRealSeconds($now->clone()->addSeconds(-30), false);
    //     $this->assertSame(-30, $res);
    // }
}
