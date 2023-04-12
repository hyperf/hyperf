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
namespace Hyperf\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Engine\Signal as EngineSignal;
use Hyperf\Signal\Annotation\Signal;
use Hyperf\Signal\SignalHandlerInterface as SignalHandler;
use Psr\Container\ContainerInterface;
use SplPriorityQueue;

class SignalManager
{
    /**
     * @var SignalHandlerInterface[][][]
     */
    protected array $handlers = [];

    protected ConfigInterface $config;

    protected bool $stopped = false;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
    }

    public function init()
    {
        foreach ($this->getQueue() as $class) {
            /** @var SignalHandlerInterface $handler */
            $handler = $this->container->get($class);
            foreach ($handler->listen() as [$process, $signal]) {
                if ($process & SignalHandler::WORKER) {
                    $this->handlers[SignalHandler::WORKER][$signal][] = $handler;
                } elseif ($process & SignalHandler::PROCESS) {
                    $this->handlers[SignalHandler::PROCESS][$signal][] = $handler;
                }
            }
        }
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }

    public function listen(?int $process)
    {
        if ($this->isInvalidProcess($process) || ! Coroutine::inCoroutine()) {
            return;
        }

        foreach ($this->handlers[$process] ?? [] as $signal => $handlers) {
            Coroutine::create(function () use ($signal, $handlers) {
                while (true) {
                    $ret = EngineSignal::wait($signal, $this->config->get('signal.timeout', 5.0));
                    if ($ret) {
                        foreach ($handlers as $handler) {
                            $handler->handle($signal);
                        }
                    }

                    if ($this->isStopped()) {
                        break;
                    }
                }
            });
        }
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    public function setStopped(bool $stopped): self
    {
        $this->stopped = $stopped;
        return $this;
    }

    protected function isInvalidProcess(?int $process): bool
    {
        return ! in_array($process, [
            SignalHandler::PROCESS,
            SignalHandler::WORKER,
        ]);
    }

    protected function getQueue(): SplPriorityQueue
    {
        $handlers = $this->config->get('signal.handlers', []);

        $queue = new SplPriorityQueue();
        foreach ($handlers as $handler => $priority) {
            if (! is_numeric($priority)) {
                $handler = $priority;
                $priority = 0;
            }
            $queue->insert($handler, $priority);
        }

        $handlers = AnnotationCollector::getClassesByAnnotation(Signal::class);
        /**
         * @var string $handler
         * @var Signal $annotation
         */
        foreach ($handlers as $handler => $annotation) {
            $queue->insert($handler, $annotation->priority ?? 0);
        }

        return $queue;
    }
}
