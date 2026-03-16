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
    protected string $name = '';

    protected string $dot = '.';

    public function __construct(ContainerInterface $container)
    {
        if ($container->has(ConfigInterface::class)) {
            if ($name = $container->get(ConfigInterface::class)->get('app_name')) {
                $this->name = $name;
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

    public function process(object $event): void
    {
        $array = [];
        if ($this->name !== '') {
            $array[] = $this->name;
        }

        if ($event instanceof OnStart) {
            $array[] = 'Master';
        } elseif ($event instanceof OnManagerStart) {
            $array[] = 'Manager';
        } elseif ($event instanceof AfterWorkerStart) {
            if ($event->server->taskworker) {
                $array[] = 'TaskWorker';
            } else {
                $array[] = 'Worker';
            }
            $array[] = $event->workerId;
        } elseif ($event instanceof BeforeProcessHandle) {
            $array[] = $event->process->name;
            $array[] = $event->index;
        }

        if ($title = implode($this->dot, $array)) {
            $this->setTitle($title);
        }
    }

    protected function setTitle(string $title)
    {
        if ($this->isSupportedOS()) {
            @cli_set_process_title($title);
        }
    }

    protected function isSupportedOS(): bool
    {
        return PHP_OS != 'Darwin';
    }
}
