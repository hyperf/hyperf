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

use Swoole\Server;

class ProcessStrategy extends WorkerStrategy
{
    protected function getNextWorkerId(Server $server): int
    {
        ++$this->currentWorkerId;
        $maxWorkerId = $server->setting['worker_num'] + $server->setting['task_worker_num'] - 1;
        if ($this->currentWorkerId > $maxWorkerId) {
            $this->currentWorkerId = 0;
        }
        return $this->currentWorkerId;
    }
}
