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

namespace Hyperf\Framework\Bootstrap;

use Hyperf\Framework\Event\OnReceive;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;

class ReceiveCallback
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public function onReceive(Server $server, int $fd, int $reactorId, $data)
    {
        $this->dispatcher->dispatch(new OnReceive($server, $fd, $reactorId, $data));
    }
}
