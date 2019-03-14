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

use Hyperf\Framework\SwooleEvent;
use Hyperf\Memory;

class ServerStartCallback
{
    public function beforeStart()
    {
        Memory\LockManager::initialize(SwooleEvent::ON_WORKER_START, SWOOLE_RWLOCK, 'workerStart');
        Memory\AtomicManager::initialize(SwooleEvent::ON_WORKER_START);
    }
}
