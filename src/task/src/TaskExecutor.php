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

namespace Hyperf\Task;

use Hyperf\Task\Exception\TaskException;
use Hyperf\Task\Exception\TaskExecuteException;
use Swoole\Server;

class TaskExecutor
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var ChannelFactory
     */
    protected $factory;

    /**
     * @var bool
     */
    protected $isTaskEnvironment = false;

    public function __construct(ChannelFactory $factory)
    {
        $this->factory = $factory;
    }

    public function setServer(Server $server): void
    {
        $this->server = $server;
        if (! isset($server->setting['task_worker_num']) || $server->setting['task_worker_num'] <= 0) {
            throw new TaskException('Missing Task Worker processes, please set server.settings.task_worker_num before use task.');
        }
    }

    public function execute(Task $task, float $timeout = 10)
    {
        $taskId = $this->server->task($task);
        if ($taskId === false) {
            throw new TaskExecuteException('Task execute failed.');
        }

        return $this->factory->pop($taskId, $timeout);
    }

    public function isTaskEnvironment(): bool
    {
        return $this->isTaskEnvironment;
    }

    public function setIsTaskEnvironment(bool $isTaskEnvironment): void
    {
        $this->isTaskEnvironment = $isTaskEnvironment;
    }
}
