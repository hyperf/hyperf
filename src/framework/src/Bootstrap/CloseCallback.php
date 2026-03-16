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

use Hyperf\Framework\Event\OnClose;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;

class CloseCallback
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public function onClose(Server $server, int $fd, int $reactorId)
    {
        $this->dispatcher->dispatch(new OnClose($server, $fd, $reactorId));
    }
}
