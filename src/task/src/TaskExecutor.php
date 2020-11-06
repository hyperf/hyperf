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
namespace Hyperf\Task;

use Hyperf\Task\Exception\TaskException;
use Hyperf\Task\Exception\TaskExecuteException;
use Hyperf\Utils\Serializer\ExceptionNormalizer;
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
     * @var ExceptionNormalizer
     */
    protected $normalizer;

    /**
     * @var bool
     */
    protected $isTaskEnvironment = true;

    public function __construct(ChannelFactory $factory, ExceptionNormalizer $normalizer)
    {
        $this->factory = $factory;
        $this->normalizer = $normalizer;
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
        if (! $this->server instanceof Server) {
            throw new TaskExecuteException('The server does not support task.');
        }

        $taskId = $this->server->task($task);
        if ($taskId === false) {
            throw new TaskExecuteException('Task execute failed.');
        }

        $result = $this->factory->pop($taskId, $timeout);

        if ($result instanceof Exception) {
            $exception = $this->normalizer->denormalize($result->attributes, $result->class);
            if ($exception instanceof \Throwable) {
                throw $exception;
            }

            throw new TaskExecuteException(get_class($exception) . ' is not instance of Throwable.');
        }

        return $result;
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
