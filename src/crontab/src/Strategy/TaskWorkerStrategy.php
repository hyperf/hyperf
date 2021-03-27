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
namespace Hyperf\Crontab\Strategy;

use Carbon\Carbon;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\PipeMessage;
use Hyperf\Server\ServerFactory;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class TaskWorkerStrategy extends AbstractStrategy
{
    /**
     * @var ServerFactory
     */
    protected $serverFactory;

    /**
     * @var int
     */
    protected $currentWorkerId = -1;

    public function __construct(ContainerInterface $container)
    {
        $this->serverFactory = $container->get(ServerFactory::class);
    }

    public function dispatch(Crontab $crontab)
    {
        $server = $this->serverFactory->getServer()->getServer();
        if ($server instanceof Server && $crontab->getExecuteTime() instanceof Carbon) {
            $workerId = $this->getNextWorkerId($server);
            $server->sendMessage(new PipeMessage(
                'callback',
                [Executor::class, 'execute'],
                $crontab
            ), $workerId);
        }
    }

    protected function getNextWorkerId(Server $server): int
    {
        ++$this->currentWorkerId;
        $minWorkerId = (int) $server->setting['worker_num'];
        $maxWorkerId = $minWorkerId + $server->setting['task_worker_num'] - 1;
        if ($this->currentWorkerId < $minWorkerId) {
            $this->currentWorkerId = $minWorkerId;
        }
        if ($this->currentWorkerId > $maxWorkerId) {
            $this->currentWorkerId = $minWorkerId;
        }
        return $this->currentWorkerId;
    }
}
