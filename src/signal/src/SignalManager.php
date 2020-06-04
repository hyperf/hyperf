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
namespace Hyperf\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Signal\Annotation\Signal;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use SplPriorityQueue;
use Swoole\Coroutine\System;

class SignalManager
{
    /**
     * @var SignalHandlerInterface[][]
     */
    protected $handlers = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var bool
     */
    protected $stoped = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function init()
    {
        foreach ($this->getHandlers() as $class) {
            /** @var SignalHandlerInterface $handler */
            $handler = $this->container->get($class);
            foreach ($handler->listen() as $signal) {
                $this->handlers[$signal][] = $handler;
            }
        }
    }

    public function listen(?string $process)
    {
        if ($this->isInvalidProcess($process)) {
            return;
        }

        foreach ($this->handlers as $signal => $handlers) {
            Coroutine::create(function () use ($signal, $handlers, $process) {
                while (true) {
                    $ret = System::waitSignal($signal, $this->config->get('signal.timeout', 5.0));
                    if ($ret) {
                        foreach ($handlers as $handler) {
                            $handler->handle($signal, $process);
                        }
                    }

                    if ($this->isStoped()) {
                        break;
                    }
                }
            });
        }
    }

    public function isStoped(): bool
    {
        return $this->stoped;
    }

    public function setStoped(bool $stoped): self
    {
        $this->stoped = $stoped;
        return $this;
    }

    protected function isInvalidProcess(?string $process): bool
    {
        return ! in_array($process, [
            SignalHandlerInterface::PROCESS,
            SignalHandlerInterface::WORKER,
        ]);
    }

    protected function getHandlers(): SplPriorityQueue
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
