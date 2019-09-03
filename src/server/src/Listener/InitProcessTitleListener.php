<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Server\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\OnManagerStart;
use Hyperf\Framework\Event\OnStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Psr\Container\ContainerInterface;

class InitProcessTitleListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            OnStart::class,
            OnManagerStart::class,
            AfterWorkerStart::class,
            BeforeProcessHandle::class,
        ];
    }

    public function process(object $event)
    {
        $prefix = 'Hyperf.';
        if ($this->container->has(ConfigInterface::class)) {
            $name = $this->container->get(ConfigInterface::class)->get('app_name');
            if ($name) {
                $prefix = $name . '.';
            }
        }

        if ($event instanceof OnStart) {
            $this->setTitle($prefix . 'Master');
        } elseif ($event instanceof OnManagerStart) {
            $this->setTitle($prefix . 'Manager');
        } elseif ($event instanceof AfterWorkerStart) {
            if ($event->server->taskworker) {
                $this->setTitle($prefix . 'TaskWorker.' . $event->workerId);
            } else {
                $this->setTitle($prefix . 'Worker.' . $event->workerId);
            }
        } elseif ($event instanceof BeforeProcessHandle) {
            $this->setTitle($prefix . $event->process->name . '.' . $event->index);
        }
    }

    protected function setTitle($title)
    {
        @cli_set_process_title($title);
    }
}
