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

    public function __construct(ChannelFactory $factory)
    {
        $this->factory = $factory;
    }

    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    public function execute(Task $task)
    {
        $taskId = $this->server->task($task);
        if ($taskId === false) {
            throw new TaskExecuteException('Task execute failed.');
        }

        return $this->factory->pop($taskId);
    }
}
