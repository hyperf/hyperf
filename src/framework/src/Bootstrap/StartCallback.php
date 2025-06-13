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

use Hyperf\Framework\Event\OnStart;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server as SwooleServer;

class StartCallback
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public function onStart(SwooleServer $server)
    {
        $this->dispatcher->dispatch(new OnStart($server));
    }
}
