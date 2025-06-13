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

use Hyperf\Framework\Event\OnManagerStart;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server as SwooleServer;

class ManagerStartCallback
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public function onManagerStart(SwooleServer $server)
    {
        $this->dispatcher->dispatch(new OnManagerStart($server));
    }
}
