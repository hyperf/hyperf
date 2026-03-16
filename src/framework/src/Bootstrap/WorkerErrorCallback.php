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

use Hyperf\Framework\Event\OnWorkerError;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;

class WorkerErrorCallback
{
    public function __construct(protected EventDispatcherInterface $dispatcher)
    {
    }

    public function onWorkerError(Server $server, int $workerId, int $workerPid, int $exitCode, int $signal)
    {
        $this->dispatcher->dispatch(new OnWorkerError($server, $workerId, $workerPid, $exitCode, $signal));
    }
}
