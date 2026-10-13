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

namespace HyperfTest\ConfigCenter;

use Hyperf\Config\Config;
use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\ConfigCenter\Event\ConfigChanged;
use Hyperf\ConfigCenter\PipeMessage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessCollector;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use Swoole\Coroutine\Socket;
use Swoole\Process;
use Swoole\Server;

/**
 * @internal
 */
#[CoversClass(AbstractDriver::class)]
class AbstractDriverBroadcastTest extends TestCase
{
    protected array $processes;

    protected Config $config;

    protected StdoutLoggerInterface $logger;

    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $property = new ReflectionProperty(ProcessCollector::class, 'processes');
        $this->processes = $property->getValue();
        $property->setValue(null, []);
        $this->config = new Config([]);
        $this->logger = Mockery::mock(StdoutLoggerInterface::class);
        $this->logger->shouldReceive('debug')->byDefault();
        $this->container = Mockery::mock(ContainerInterface::class);
        $this->container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($this->config);
        $this->container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn($this->logger);
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            (new ReflectionProperty(ProcessCollector::class, 'processes'))->setValue(null, $this->processes);
        }
    }

    #[DataProvider('getSelfPositions')]
    public function testBroadcastSkipsSelfAndUpdatesLocalConfigOnce(int $position)
    {
        $message = new PipeMessage(['test' => ['version' => 2]]);
        $self = $this->getSelfProcess();
        for ($index = 0; $index < 3; ++$index) {
            ProcessCollector::add('test', $index === $position ? $self : $this->getRemoteProcess($message));
        }
        // Multiple registrations must not apply the same local update twice.
        ProcessCollector::add('duplicate', $self);
        $driver = $this->getDriver();
        $driver->shouldReceive('onPipeMessage')->once()->with($message)->passthru();

        (new ReflectionMethod(AbstractDriver::class, 'shareMessageToUserProcesses'))->invoke($driver, $message);

        $this->assertSame(['version' => 2], $this->config->get('test'));
    }

    public static function getSelfPositions(): array
    {
        return [[0], [1], [2]];
    }

    public function testBroadcastWithOnlySelf()
    {
        ProcessCollector::add('self', $this->getSelfProcess());
        $driver = $this->getDriver();
        $message = new PipeMessage(['test' => 'local']);
        $driver->shouldReceive('onPipeMessage')->once()->with($message)->passthru();

        (new ReflectionMethod(AbstractDriver::class, 'shareMessageToUserProcesses'))->invoke($driver, $message);

        $this->assertSame('local', $this->config->get('test'));
    }

    public function testBroadcastWithoutSelfDoesNotUpdateLocalConfig()
    {
        $message = new PipeMessage(['test' => 'remote']);
        ProcessCollector::add('remote', $this->getRemoteProcess($message));
        ProcessCollector::add('unstarted', $this->getRemoteProcess($message, null));
        ProcessCollector::add('zero', $this->getRemoteProcess($message, 0));
        $driver = $this->getDriver();
        $driver->shouldNotReceive('onPipeMessage');

        (new ReflectionMethod(AbstractDriver::class, 'shareMessageToUserProcesses'))->invoke($driver, $message);

        $this->assertNull($this->config->get('test'));
    }

    public function testBroadcastWithEmptyCollector()
    {
        $driver = $this->getDriver();
        $driver->shouldNotReceive('onPipeMessage');

        (new ReflectionMethod(AbstractDriver::class, 'shareMessageToUserProcesses'))->invoke($driver, new PipeMessage(['test' => 'value']));

        $this->assertNull($this->config->get('test'));
    }

    public function testSendFailureDoesNotPreventRemainingNotifications()
    {
        $message = new PipeMessage(['test' => 'updated']);
        ProcessCollector::add('self', $this->getSelfProcess());
        ProcessCollector::add('failed', $this->getRemoteProcess($message, -1, false));
        ProcessCollector::add('remote', $this->getRemoteProcess($message));
        $this->logger->shouldReceive('error')->once()->with('Configuration synchronization failed. Please restart the server.');
        $driver = $this->getDriver();
        $driver->shouldReceive('onPipeMessage')->once()->with($message)->passthru();

        (new ReflectionMethod(AbstractDriver::class, 'shareMessageToUserProcesses'))->invoke($driver, $message);

        $this->assertSame('updated', $this->config->get('test'));
    }

    public function testLocalExceptionOccursAfterRemoteNotifications()
    {
        $message = new PipeMessage(['test' => 'value']);
        ProcessCollector::add('self', $this->getSelfProcess());
        ProcessCollector::add('remote', $this->getRemoteProcess($message));
        $driver = $this->getDriver();
        $driver->shouldReceive('onPipeMessage')->once()->with($message)->andThrow(new RuntimeException('Local update failed'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Local update failed');
        (new ReflectionMethod(AbstractDriver::class, 'shareMessageToUserProcesses'))->invoke($driver, $message);
    }

    public function testSyncConfigWithEmptyCollectorUpdatesLocallyAndDispatchesEvent()
    {
        $config = ['test' => 'new'];
        $previous = ['test' => 'old'];
        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')->once()->with(Mockery::on(function ($event) use ($config, $previous) {
            $this->assertEquals(new ConfigChanged($config, $previous), $event);
            $this->assertSame('new', $this->config->get('test'));
            return true;
        }))->andReturnArg(0);
        $this->container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($dispatcher);

        (new ReflectionMethod(AbstractDriver::class, 'syncConfig'))->invoke($this->getDriver(), $config, $previous);

        $this->assertSame('new', $this->config->get('test'));
    }

    public function testSyncConfigNotifiesWorkersAndUserProcesses()
    {
        $message = new PipeMessage(['test' => 'new']);
        ProcessCollector::add('self', $this->getSelfProcess());
        ProcessCollector::add('remote', $this->getRemoteProcess($message));
        $server = Mockery::mock(Server::class);
        $server->setting = ['worker_num' => 2, 'task_worker_num' => 1];
        for ($workerId = 0; $workerId < 3; ++$workerId) {
            $server->shouldReceive('sendMessage')->once()->with(Mockery::on(fn ($actual) => $actual == $message), $workerId)->andReturnTrue();
        }
        $driver = $this->getDriver();
        $driver->setServer($server);

        (new ReflectionMethod(AbstractDriver::class, 'syncConfig'))->invoke($driver, $message->getData());

        $this->assertSame('new', $this->config->get('test'));
    }

    protected function getDriver(): AbstractDriver
    {
        return Mockery::mock(AbstractDriver::class, [$this->container])->makePartial();
    }

    protected function getSelfProcess(): Process
    {
        $process = Mockery::mock(Process::class);
        $process->pid = getmypid();
        $process->shouldNotReceive('exportSocket');
        return $process;
    }

    protected function getRemoteProcess(PipeMessage $message, ?int $pid = -1, bool $success = true): Process
    {
        $socket = Mockery::mock(Socket::class);
        $socket->shouldReceive('send')->once()->with(serialize($message), 10)->andReturnUsing(function () use ($message, $success) {
            // Local application must happen after all remote sends, regardless of registration order.
            $this->assertNull($this->config->get('test'));
            return $success ? strlen(serialize($message)) : false;
        });
        $process = Mockery::mock(Process::class);
        $process->pid = $pid;
        $process->shouldReceive('exportSocket')->once()->andReturn($socket);
        return $process;
    }
}
