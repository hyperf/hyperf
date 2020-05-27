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
use Swoole\Coroutine\System;

class SignalManager
{
    /**
     * @var SignalHandlerInterface[][]
     */
    protected $handlers;

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

    public function listen()
    {
        foreach ($this->handlers as $signal => $handlers) {
            Coroutine::create(function () use ($signal, $handlers) {
                while (true) {
                    $ret = System::waitSignal($signal, $this->config->get('signal.timeout', 5.0));
                    if ($ret) {
                        foreach ($handlers as $handler) {
                            $handler->handle($signal);
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

    protected function getHandlers(): array
    {
        $handlers = $this->config->get('signal.handlers', []);

        $handlers = array_merge($handlers, array_keys(AnnotationCollector::getClassesByAnnotation(Signal::class)));

        return array_unique($handlers);
    }
}
