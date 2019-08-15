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

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\OnManagerStart;
use Hyperf\Framework\Event\OnStart;
use Hyperf\Process\Event\BeforeProcessHandle;

class InitProcessTitleListener implements ListenerInterface
{
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
            @cli_set_process_title('Master');
        } elseif ($event instanceof OnManagerStart) {
            @cli_set_process_title('Manager');
        } elseif ($event instanceof AfterWorkerStart) {
            if ($event->server->taskworker) {
                @cli_set_process_title('TaskWorker.' . $event->workerId);
            } else {
                @cli_set_process_title('Worker.' . $event->workerId);
            }
        } elseif ($event instanceof BeforeProcessHandle) {
            @cli_set_process_title($event->process->name . '.' . $event->index);
        }
    }
}
