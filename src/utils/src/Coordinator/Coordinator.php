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
namespace Hyperf\Utils\Coordinator;

use Hyperf\Engine\Channel;

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
     *
     * @param float|int $timeout
     * @return bool returns true if the coordinator has been woken up
     */
    public function yield($timeout = -1): bool
    {
        $this->channel->pop((float) $timeout);
        return $this->channel->isClosing();
    }

    /**
     * Wakeup all coroutines yielding for this coordinator.
     */
    public function resume(): void
    {
        $this->channel->close();
    }
}
