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
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $dot = '.';

    public function __construct(ContainerInterface $container)
    {
        if ($container->has(ConfigInterface::class)) {
            if ($name = $container->get(ConfigInterface::class)->get('app_name')) {
                $this->prefix = $name . $this->dot;
            }
        }
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
        if ($event instanceof OnStart) {
            $this->setTitle('Master');
        } elseif ($event instanceof OnManagerStart) {
            $this->setTitle('Manager');
        } elseif ($event instanceof AfterWorkerStart) {
            if ($event->server->taskworker) {
                $this->setTitle('TaskWorker.' . $event->workerId);
            } else {
                $this->setTitle('Worker.' . $event->workerId);
            }
        } elseif ($event instanceof BeforeProcessHandle) {
            $this->setTitle($event->process->name . '.' . $event->index);
        }
    }

    protected function setTitle($title)
    {
        @cli_set_process_title($this->prefix . $title);
    }
}
