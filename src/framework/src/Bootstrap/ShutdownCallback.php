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

use Hyperf\Framework\Event\OnShutdown;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;

class ShutdownCallback
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public function onShutdown(Server $server)
    {
        $this->dispatcher->dispatch(new OnShutdown($server));
    }
}
