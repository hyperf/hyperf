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
namespace Hyperf\Process;

use Hyperf\Contract\ProcessInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Constant;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Process\Event\AfterCoroutineHandle;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Process\Event\BeforeCoroutineHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Process\Event\PipeMessage;
use Hyperf\Process\Exception\ServerInvalidException;
use Hyperf\Process\Exception\SocketAcceptException;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\Channel;
use Swoole\Event;
use Swoole\Process as SwooleProcess;
use Swoole\Server;
use Swoole\Timer;

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
     * @var null|SwooleProcess
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
     * @var int
     */
    protected $restartInterval = 5;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        if ($container->has(EventDispatcherInterface::class)) {
            $this->event = $container->get(EventDispatcherInterface::class);
        }
    }

    public function isEnable($server): bool
    {
        return true;
    }

    public function bind($server): void
    {
        if (Constant::isCoroutineServer($server)) {
            $this->bindCoroutineServer($server);
            return;
        }

        if ($server instanceof Server) {
            $this->bindServer($server);
            return;
        }

        throw new ServerInvalidException(sprintf('Server %s is invalid.', get_class($server)));
    }

    protected function bindServer(Server $server): void
    {
        $num = $this->nums;
        for ($i = 0; $i < $num; ++$i) {
            $process = new SwooleProcess(function (SwooleProcess $process) use ($i) {
                try {
                    $this->event && $this->event->dispatch(new BeforeProcessHandle($this, $i));

                    $this->process = $process;
                    if ($this->enableCoroutine) {
                        $quit = new Channel(1);
                        $this->listen($quit);
                    }
                    $this->handle();
                } catch (\Throwable $throwable) {
                    $this->logThrowable($throwable);
                } finally {
                    $this->event && $this->event->dispatch(new AfterProcessHandle($this, $i));
                    if (isset($quit)) {
                        $quit->push(true);
                    }
                    Timer::clearAll();
                    sleep($this->restartInterval);
                }
            }, $this->redirectStdinStdout, $this->pipeType, $this->enableCoroutine);
            $server->addProcess($process);

            if ($this->enableCoroutine) {
                ProcessCollector::add($this->name, $process);
            }
        }
    }

    protected function bindCoroutineServer($server): void
    {
        $num = $this->nums;
        for ($i = 0; $i < $num; ++$i) {
            $handler = function () use ($i) {
                $this->event && $this->event->dispatch(new BeforeCoroutineHandle($this, $i));
                while (true) {
                    try {
                        $this->handle();
                    } catch (\Throwable $throwable) {
                        $this->logThrowable($throwable);
                    }

                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($this->restartInterval)) {
                        break;
                    }
                }
                $this->event && $this->event->dispatch(new AfterCoroutineHandle($this, $i));
            };

            Coroutine::create($handler);
        }
    }

    /**
     * Added event for listening data from worker/task.
     */
    protected function listen(Channel $quit)
    {
        Coroutine::create(function () use ($quit) {
            while ($quit->pop(0.001) !== true) {
                try {
                    /** @var \Swoole\Coroutine\Socket $sock */
                    $sock = $this->process->exportSocket();
                    $recv = $sock->recv($this->recvLength, $this->recvTimeout);
                    if ($recv === '') {
                        throw new SocketAcceptException('Socket is closed', $sock->errCode);
                    }

                    if ($recv === false && $sock->errCode !== SOCKET_ETIMEDOUT) {
                        throw new SocketAcceptException('Socket is closed', $sock->errCode);
                    }

                    if ($this->event && $recv !== false && $data = unserialize($recv)) {
                        $this->event->dispatch(new PipeMessage($data));
                    }
                } catch (\Throwable $exception) {
                    $this->logThrowable($exception);
                    if ($exception instanceof SocketAcceptException) {
                        // TODO: Reconnect the socket.
                        break;
                    }
                }
            }
            $quit->close();
        });
    }

    protected function logThrowable(\Throwable $throwable): void
    {
        if ($this->container->has(StdoutLoggerInterface::class) && $this->container->has(FormatterInterface::class)) {
            $logger = $this->container->get(StdoutLoggerInterface::class);
            $formatter = $this->container->get(FormatterInterface::class);
            $logger->error($formatter->format($throwable));

            if ($throwable instanceof SocketAcceptException) {
                $logger->critical('Socket of process is unavailable, please restart the server');
            }
        }
    }
}
