<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Framework\Bootstrap;

use Hyperf\Framework\Event\OnWorkerExit;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;

class WorkerExitCallback
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
    }

    public function onWorkerExit(Server $server, int $workerId)
    {
        $this->dispatcher->dispatch(new OnWorkerExit($server, $workerId));
    }
}
