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

namespace Hyperf\Process;

use Hyperf\Contract\ProcessInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Process\Event\PipeMessage;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Event;
use Swoole\Process as SwooleProcess;
use Swoole\Server;

abstract class AbstractProcess implements ProcessInterface
{
    /**
     * @var string
     */
    public $name = 'process';

    /**
     * @var int
     */
    public $nums = 1;

    /**
     * @var bool
     */
    public $redirectStdinStdout = false;

    /**
     * @var int
     */
    public $pipeType = SOCK_DGRAM;

    /**
     * @var bool
     */
    public $enableCoroutine = true;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var null|EventDispatcherInterface
     */
    protected $event;

    /**
     * @var SwooleProcess
     */
    protected $process;

    /**
     * @var int
     */
    protected $recvLength = 65535;

    /**
     * @var float
     */
    protected $recvTimeout = 10.0;

    /**
     * @var bool
     */
    protected $listening = true;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        if ($container->has(EventDispatcherInterface::class)) {
            $this->event = $container->get(EventDispatcherInterface::class);
        }
    }

    public function isEnable(): bool
    {
        return true;
    }

    public function bind(Server $server): void
    {
        $num = $this->nums;
        for ($i = 0; $i < $num; ++$i) {
            $process = new SwooleProcess(function (SwooleProcess $process) use ($i) {
                try {
                    $this->event && $this->event->dispatch(new BeforeProcessHandle($this, $i));

                    $this->process = $process;
                    $this->listen();
                    $this->handle();

                    $this->event && $this->event->dispatch(new AfterProcessHandle($this, $i));
                } finally {
                    $this->listening = false;
                }
            }, $this->redirectStdinStdout, $this->pipeType, $this->enableCoroutine);
            $server->addProcess($process);

            if ($this->enableCoroutine) {
                ProcessCollector::add($this->name, $process);
            }
        }
    }

    /**
     * Added event for listening data from worker/task.
     */
    protected function listen()
    {
        go(function () {
            while ($this->listening) {
                try {
                    /** @var \Swoole\Coroutine\Socket $sock */
                    $sock = $this->process->exportSocket();
                    $recv = $sock->recv($this->recvLength, $this->recvTimeout);
                    if ($this->event && $recv !== false && $data = unserialize($recv)) {
                        $this->event->dispatch(new PipeMessage($data));
                    }
                } catch (\Throwable $exception) {
                    if ($this->container->has(StdoutLoggerInterface::class) && $this->container->has(FormatterInterface::class)) {
                        $logger = $this->container->get(StdoutLoggerInterface::class);
                        $formatter = $this->container->get(FormatterInterface::class);
                        $logger->error($formatter->format($exception));
                    }
                }
            }
        });
    }
}
