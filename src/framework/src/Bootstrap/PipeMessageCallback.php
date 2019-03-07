<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Framework\Bootstrap;

use Hyperf\Framework\Event\OnPipeMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server as SwooleServer;

class PipeMessageCallback
{

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle Swoole onWorkerStop event.
     */
    public function onPipeMessage(SwooleServer $server, int $fromWorkerId, $data)
    {
        $this->eventDispatcher->dispatch(new OnPipeMessage($server, $fromWorkerId, $data));
    }
}
