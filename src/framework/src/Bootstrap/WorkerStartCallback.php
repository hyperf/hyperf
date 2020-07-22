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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Framework\Event\OtherWorkerStart;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server as SwooleServer;

class WorkerStartCallback
{
    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(StdoutLoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle Swoole onWorkerStart event.
     */
    public function onWorkerStart(SwooleServer $server, int $workerId)
    {
        $this->eventDispatcher->dispatch(new BeforeWorkerStart($server, $workerId));

        if ($workerId === 0) {
            $this->eventDispatcher->dispatch(new MainWorkerStart($server, $workerId));
        } else {
            $this->eventDispatcher->dispatch(new OtherWorkerStart($server, $workerId));
        }

        if ($server->taskworker) {
            $this->logger->info("TaskWorker#{$workerId} started.");
        } else {
            $this->logger->info("Worker#{$workerId} started.");
        }

        $this->eventDispatcher->dispatch(new AfterWorkerStart($server, $workerId));
        CoordinatorManager::until(Constants::WORKER_START)->resume();
    }
}
