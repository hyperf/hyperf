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

use Hyperf\Framework\Event\OnPipeMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server as SwooleServer;

class PipeMessageCallback
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * Handle Swoole onWorkerStop event.
     * @param mixed $data
     */
    public function onPipeMessage(SwooleServer $server, int $fromWorkerId, $data)
    {
        $this->dispatcher->dispatch(new OnPipeMessage($server, $fromWorkerId, $data));
    }
}
