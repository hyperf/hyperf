<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Pool;

use Hyperf\Utils\Coroutine;
use Swoole\Coroutine\Channel as CoChannel;

class Channel
{
    protected $size;

    /**
     * @var CoChannel
     */
    protected $channel;

    /**
     * @var \SplQueue
     */
    protected $queue;

    public function __construct(int $size)
    {
        $this->size = $size;
        $this->channel = new CoChannel($size);
        $this->queue = new \SplQueue();
    }

    public function pop(float $timeout)
    {
        if ($this->isCoroutine()) {
            return $this->channel->pop($timeout);
        }
        return $this->queue->shift();
    }

    public function push($data)
    {
        if ($this->isCoroutine()) {
            return $this->channel->push($data);
        }
        return $this->queue->push($data);
    }

    public function length(): int
    {
        if ($this->isCoroutine()) {
            return $this->channel->length();
        }
        return $this->queue->count();
    }

    protected function isCoroutine(): bool
    {
        return Coroutine::id() > 0;
    }
}
