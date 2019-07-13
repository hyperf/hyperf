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

namespace Hyperf\Framework\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Swoole\Server;

class OnTask implements StoppableEventInterface
{
    /**
     * @var Server
     */
    public $server;

    /**
     * @var int
     */
    public $taskId;

    /**
     * @var int
     */
    public $srcWorkerId;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @var mixed
     */
    public $result;

    public function __construct(Server $server, int $taskId, int $srcWorkerId, mixed $data)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->srcWorkerId = $srcWorkerId;
        $this->data = $data;
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
