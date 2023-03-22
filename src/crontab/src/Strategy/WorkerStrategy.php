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

use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\PipeMessage;
use Hyperf\Server\ServerFactory;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class WorkerStrategy extends AbstractStrategy
{
    protected ServerFactory $serverFactory;

    protected int $currentWorkerId = -1;

    public function __construct(ContainerInterface $container)
    {
        $this->serverFactory = $container->get(ServerFactory::class);

        parent::__construct($container);
    }

    public function dispatch(Crontab $crontab)
    {
        $server = $this->serverFactory->getServer()->getServer();
        if ($server instanceof Server) {
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
        $maxWorkerId = $server->setting['worker_num'] - 1;
        if ($this->currentWorkerId > $maxWorkerId) {
            $this->currentWorkerId = 0;
        }
        return $this->currentWorkerId;
    }
}
