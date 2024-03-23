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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Constant;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Process\Event\AfterCoroutineHandle;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Process\Event\BeforeCoroutineHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Process\Event\PipeMessage;
use Hyperf\Process\Exception\ServerInvalidException;
use Hyperf\Process\Exception\SocketAcceptException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\Socket;
use Swoole\Event;
use Swoole\Process as SwooleProcess;
use Swoole\Server;
use Swoole\Timer;
use Throwable;

abstract class AbstractProcess implements ProcessInterface
{
    public string $name = 'process';

    public int $nums = 1;

    public bool $redirectStdinStdout = false;

    public int $pipeType = SOCK_DGRAM;

    public bool $enableCoroutine = true;

    protected ?EventDispatcherInterface $event = null;

    protected ?SwooleProcess $process = null;

    protected int $recvLength = 65535;

    protected float $recvTimeout = 10.0;

    protected int $restartInterval = 5;

    public function __construct(protected ContainerInterface $container)
    {
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
                    $this->event?->dispatch(new BeforeProcessHandle($this, $i));

                    $this->process = $process;
                    if ($this->enableCoroutine) {
                        $quit = new Channel(1);
                        $this->listen($quit);
                    }
                    $this->handle();
                } catch (Throwable $throwable) {
                    $this->logThrowable($throwable);
                } finally {
                    $this->event?->dispatch(new AfterProcessHandle($this, $i));
                    if (isset($quit)) {
                        $quit->push(true);
                    }
                    Timer::clearAll();
                    CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
                    sleep($this->restartInterval);
                }
            }, $this->redirectStdinStdout, $this->pipeType, $this->enableCoroutine);
            $process->setBlocking(false);
            $server->addProcess($process);

            if ($this->enableCoroutine) {
                ProcessCollector::add($this->name, $process);
            }
        }
    }

    protected function bindCoroutineServer($server): void
    {
        $num = $this->nums;
        Coroutine::create(static function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                ProcessManager::setRunning(false);
            }
        });

        for ($i = 0; $i < $num; ++$i) {
            $handler = function () use ($i) {
                $this->event?->dispatch(new BeforeCoroutineHandle($this, $i));
                while (true) {
                    try {
                        $this->handle();
                    } catch (Throwable $throwable) {
                        $this->logThrowable($throwable);
                    }

                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($this->restartInterval)) {
                        break;
                    }
                }
                $this->event?->dispatch(new AfterCoroutineHandle($this, $i));
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
                    /** @var Socket $sock */
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
                } catch (Throwable $exception) {
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

    protected function logThrowable(Throwable $throwable): void
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
