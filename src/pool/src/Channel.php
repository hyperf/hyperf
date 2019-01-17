<?php
/**
 * Created by PhpStorm.
 * User: limx
 * Date: 2019/1/17
 * Time: 2:18 PM
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

    protected function isCoroutine()
    {
        return Coroutine::id() > 0;
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
}