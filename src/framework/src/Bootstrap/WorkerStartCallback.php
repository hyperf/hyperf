<?php

namespace Hyperflex\Framework\Bootstrap;

use Hyperflex\Framework\Constants\SwooleEvent;
use Hyperflex\Framework\Contract\StdoutLoggerInterface;
use Hyperflex\Di\Container;
use Hyperflex\Memory\AtomicManager;
use Hyperflex\Memory\LockManager;
use Psr\Container\ContainerInterface;
use Swoole\Server as SwooleServer;

class WorkerStartCallback
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container, StdoutLoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Handle Swoole onWorkerStart event.
     */
    public function onWorkerStart(SwooleServer $server, int $workerId)
    {
        // Atomic and Lock have to initializes before worker start.
        $atomic = AtomicManager::get(SwooleEvent::ON_WORKER_START);
        $lock = LockManager::get(SwooleEvent::ON_WORKER_START);
        $isScan = false;
        $lockedWorkerId = null;
        if ($lock->trylock()) {
            $lockedWorkerId = $workerId;
            // Only running in one worker.
            $this->logger->debug("Worker $lockedWorkerId got the lock.");
            // @TODO Do something that you want only one worker do.
            sleep(1);
            $lock->unlock();
            $atomic->wakeup($server->setting['worker_num'] - 1);
        } else {
            $this->logger->debug("Worker $workerId wating ...");
            $atomic->wait();
        }
        if (! $isScan || $workerId !== $lockedWorkerId) {
            // @TODO Do something that the workers who does not got the lock should do.
        }
        $this->logger->info("Worker $workerId started.");
        LockManager::clear(SwooleEvent::ON_WORKER_START);
        AtomicManager::clear(SwooleEvent::ON_WORKER_START);
    }

}