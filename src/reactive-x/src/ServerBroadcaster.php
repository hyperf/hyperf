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

use Hyperf\Contract\ContainerInterface;
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
     *
     * @var int
     */
    protected $id;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, ?int $id = null)
    {
        $this->container = $container;
        $this->id = $id;
    }

    public function broadcast(IpcMessageWrapper $message)
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
