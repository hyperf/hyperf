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
namespace Hyperf\Framework\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\Event\OnTask;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;
use Swoole\Server\Task;

class TaskCallback
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var bool
     */
    protected $taskEnableCoroutine = false;

    public function __construct(ConfigInterface $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
        $this->taskEnableCoroutine = $config->get('server.settings.task_enable_coroutine', false);
    }

    public function onTask(Server $server, ...$arguments)
    {
        if ($this->taskEnableCoroutine) {
            $task = $arguments[0];
        } else {
            [$taskId, $srcWorkerId, $data] = $arguments;
            $task = new Task();
            $task->id = $taskId;
            $task->worker_id = $srcWorkerId;
            $task->data = $data;
        }

        $event = $this->dispatcher->dispatch(new OnTask($server, $task));

        if ($event instanceof OnTask && ! is_null($event->result)) {
            if ($this->taskEnableCoroutine) {
                $task->finish($event->result);
            } else {
                $server->finish($event->result);
            }
        }
    }
}
