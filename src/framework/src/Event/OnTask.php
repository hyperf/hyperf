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
    /**
     * @var Server
     */
    public $server;

    /**
     * @var Task
     */
    public $task;

    /**
     * @var mixed
     */
    public $result;

    public function __construct(Server $server, Task $task)
    {
        $this->server = $server;
        $this->task = $task;
    }

    /**
     * @param mixed $result
     * @return OnTask
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    public function isPropagationStopped(): bool
    {
        return ! is_null($this->result);
    }
}
