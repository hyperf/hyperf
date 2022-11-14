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
namespace Hyperf\Crontab\Strategy;

use Carbon\Carbon;
use Closure;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Timer;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Event\FailToExecute;
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Crontab\Mutex\RedisServerMutex;
use Hyperf\Crontab\Mutex\RedisTaskMutex;
use Hyperf\Crontab\Mutex\ServerMutex;
use Hyperf\Crontab\Mutex\TaskMutex;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Throwable;

class Executor
{
    protected ?PsrLoggerInterface $logger = null;

    protected ?TaskMutex $taskMutex = null;

    protected ?ServerMutex $serverMutex = null;

    protected ?EventDispatcherInterface $dispatcher = null;

    protected Timer $timer;

    public function __construct(protected ContainerInterface $container)
    {
        if ($container->has(LoggerInterface::class)) {
            $this->logger = $container->get(LoggerInterface::class);
        } elseif ($container->has(StdoutLoggerInterface::class)) {
            $this->logger = $container->get(StdoutLoggerInterface::class);
        }
        if ($container->has(EventDispatcherInterface::class)) {
            $this->dispatcher = $container->get(EventDispatcherInterface::class);
        }

        $this->timer = new Timer($this->logger);
    }

    public function execute(Crontab $crontab)
    {
        if (! $crontab->getExecuteTime()) {
            return;
        }
        $diff = $crontab->getExecuteTime()->diffInRealSeconds(new Carbon());
        $callback = null;
        switch ($crontab->getType()) {
            case 'callback':
                [$class, $method] = $crontab->getCallback();
                $parameters = $crontab->getCallback()[2] ?? null;
                if ($class && $method && class_exists($class) && method_exists($class, $method)) {
                    $callback = function () use ($class, $method, $parameters, $crontab) {
                        $runnable = function () use ($class, $method, $parameters, $crontab) {
                            try {
                                $result = true;
                                $instance = make($class);
                                if ($parameters && is_array($parameters)) {
                                    $instance->{$method}(...$parameters);
                                } else {
                                    $instance->{$method}();
                                }
                            } catch (Throwable $throwable) {
                                $result = false;
                                $this->dispatcher && $this->dispatcher->dispatch(new FailToExecute($crontab, $throwable));
                            } finally {
                                $this->logResult($crontab, $result, $throwable ?? null);
                            }
                        };

                        Coroutine::create($this->decorateRunnable($crontab, $runnable));
                    };
                }
                break;
            case 'command':
                $input = make(ArrayInput::class, [$crontab->getCallback()]);
                $output = make(NullOutput::class);
                $application = $this->container->get(ApplicationInterface::class);
                $application->setAutoExit(false);
                $callback = function () use ($application, $input, $output, $crontab) {
                    $runnable = function () use ($application, $input, $output, $crontab) {
                        $result = $application->run($input, $output);
                        $this->logResult($crontab, $result === 0);
                    };
                    $this->decorateRunnable($crontab, $runnable)();
                };
                break;
            case 'eval':
                $callback = function () use ($crontab) {
                    $runnable = function () use ($crontab) {
                        eval($crontab->getCallback());
                    };
                    $this->decorateRunnable($crontab, $runnable)();
                };
                break;
        }
        $callback && $this->timer->after($diff > 0 ? $diff : 1, $callback);
    }

    protected function runInSingleton(Crontab $crontab, Closure $runnable): Closure
    {
        return function () use ($crontab, $runnable) {
            $taskMutex = $this->getTaskMutex();

            if ($taskMutex->exists($crontab) || ! $taskMutex->create($crontab)) {
                $this->logger?->info(sprintf('Crontab task [%s] skipped execution at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
                return;
            }

            try {
                $runnable();
            } finally {
                $taskMutex->remove($crontab);
            }
        };
    }

    protected function getTaskMutex(): TaskMutex
    {
        if (! $this->taskMutex) {
            $this->taskMutex = $this->container->has(TaskMutex::class)
                ? $this->container->get(TaskMutex::class)
                : $this->container->get(RedisTaskMutex::class);
        }
        return $this->taskMutex;
    }

    protected function runOnOneServer(Crontab $crontab, Closure $runnable): Closure
    {
        return function () use ($crontab, $runnable) {
            $taskMutex = $this->getServerMutex();

            if (! $taskMutex->attempt($crontab)) {
                $this->logger?->info(sprintf('Crontab task [%s] skipped execution at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
                return;
            }

            $runnable();
        };
    }

    protected function getServerMutex(): ServerMutex
    {
        if (! $this->serverMutex) {
            $this->serverMutex = $this->container->has(ServerMutex::class)
                ? $this->container->get(ServerMutex::class)
                : $this->container->get(RedisServerMutex::class);
        }
        return $this->serverMutex;
    }

    protected function decorateRunnable(Crontab $crontab, Closure $runnable): Closure
    {
        if ($crontab->isSingleton()) {
            $runnable = $this->runInSingleton($crontab, $runnable);
        }

        if ($crontab->isOnOneServer()) {
            $runnable = $this->runOnOneServer($crontab, $runnable);
        }

        return $runnable;
    }

    protected function logResult(Crontab $crontab, bool $isSuccess, ?Throwable $throwable = null)
    {
        if ($isSuccess) {
            $this->logger?->info(sprintf('Crontab task [%s] executed successfully at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
        } else {
            $this->logger?->error(sprintf('Crontab task [%s] failed execution at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
            $throwable && $this->logger?->error((string) $throwable);
        }
    }
}
