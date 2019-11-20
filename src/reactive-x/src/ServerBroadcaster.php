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

namespace Hyperf\ReactiveX;

use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Swoole\Server;

class ServerBroadcaster implements BroadcasterInterface
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * WorkerId.
     * @var int
     */
    protected $id;

    public function __construct(Server $server, ?int $id = null)
    {
        $this->server = $server;
        $this->id = $id;
    }

    public function broadcast(IpcMessageWrapper $message)
    {
        if ($this->id !== null) {
            $this->server->sendMessage($message, $this->id);
            return;
        }

        $workerCount = $this->server->setting['worker_num'] + $this->server->setting['task_worker_num'] - 1;
        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            if ($workerId === $this->server->worker_id) {
                continue;
            }
            $this->server->sendMessage($message, $workerId);
        }
    }
}
