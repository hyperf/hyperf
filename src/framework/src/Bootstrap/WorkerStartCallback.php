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

use Hyperf\Di\Container;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\Framework\SwooleEvent;
use Hyperf\Memory\AtomicManager;
use Hyperf\Memory\LockManager;
use Psr\Container\ContainerInterface;
use RuntimeException;
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
        try {
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
        } catch (RuntimeException $e) {
            $this->logger->warning('Worker atomic and lock initialize fail.');
        } finally {
            LockManager::clear(SwooleEvent::ON_WORKER_START);
            AtomicManager::clear(SwooleEvent::ON_WORKER_START);
        }
    }
}
