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

use Hyperf\Framework\Event\OnFinish;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;

class FinishCallback
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public function onFinish(Server $server, int $taskId, $data)
    {
        $this->dispatcher->dispatch(new OnFinish($server, $taskId, $data));
    }
}
