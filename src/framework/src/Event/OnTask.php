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

namespace Hyperf\Framework\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Swoole\Server;
use Swoole\Server\Task;

class OnTask implements StoppableEventInterface
{
    public mixed $result = null;

    public function __construct(public Server $server, public Task $task)
    {
    }

    public function setResult(mixed $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function isPropagationStopped(): bool
    {
        return ! is_null($this->result);
    }
}
