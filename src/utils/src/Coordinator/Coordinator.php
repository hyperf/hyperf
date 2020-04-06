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

namespace Hyperf\Utils\Coordinator;

use Swoole\Coroutine\Channel;

class Coordinator
{
    /**
     * @var Channel
     */
    private $channel;

    public function __construct()
    {
        $this->channel = new Channel(1);
    }

    /**
     * Yield the current coroutine for a given timeout,
     * unless the coordinator is woke up from outside.
     * @return bool returns true if the coordinator has been woken up
     */
    public function yield(float $timeout = -1): bool
    {
        $this->channel->pop($timeout);
        $code = $this->channel->errCode;
        if ($code == -2) {
            return true;
        }
        return false;
    }

    /**
     * Wakeup all coroutines yielding for this coordinator.
     */
    public function resume()
    {
        $this->channel->close();
    }
}
