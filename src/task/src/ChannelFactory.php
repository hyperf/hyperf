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

use Hyperf\Task\Exception\TaskExecuteTimeoutException;
use Swoole\Coroutine\Channel;

class ChannelFactory
{
    protected $channels = [];

    public function get(int $taskId): ?Channel
    {
        if ($this->has($taskId)) {
            return $this->channels[$taskId];
        }

        return $this->channels[$taskId] = new Channel(1);
    }

    public function pop(int $taskId, float $timeout = 10)
    {
        $channel = $this->get($taskId);

        $result = $channel->pop($timeout);
        if (! $result instanceof TaskData) {
            $this->channels[$taskId] = null;
            throw new TaskExecuteTimeoutException(sprintf('Task [%d] execute timeout.', $taskId));
        }

        // Removed channel from factory.
        $this->remove($taskId);
        return $result->data;
    }

    public function push(int $taskId, $data): void
    {
        $channel = $this->get($taskId);

        if ($channel instanceof Channel) {
            $channel->push(new TaskData($taskId, $data));
        } else {
            // Task execute timeout, discard data and remove it from factory.
            $this->remove($taskId);
        }
    }

    public function has(int $taskId): bool
    {
        return array_key_exists($taskId, $this->channels);
    }

    public function remove(int $taskId): void
    {
        unset($this->channels[$taskId]);
    }
}
