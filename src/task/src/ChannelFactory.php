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

use Swoole\Coroutine\Channel;

class ChannelFactory
{
    protected $channels = [];

    public function get(int $taskId)
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
        unset($this->channels[$taskId]);

        return $result;
    }

    public function has(int $taskId)
    {
        return isset($this->channels[$taskId]) && $this->channels[$taskId] instanceof Channel;
    }
}
