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

namespace Hyperf\ReactiveX;

use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class ServerBroadcaster implements BroadcasterInterface
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @param null|int $id WorkerId
     */
    public function __construct(
        private ContainerInterface $container,
        protected ?int $id = null
    ) {
    }

    public function broadcast(IpcMessageWrapper $message): void
    {
        // Lazy load to avoid causing issue before sever starts.
        if ($this->server === null) {
            $this->server = $this->container->get(Server::class);
        }

        if ($this->id !== null) {
            $this->server->sendMessage($message, $this->id);
            return;
        }

        $workerCount = $this->server->setting['worker_num'] - 1;
        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            if ($workerId === $this->server->worker_id) {
                continue;
            }
            $this->server->sendMessage($message, $workerId);
        }
    }
}
