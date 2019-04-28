<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Framework\Bootstrap;

use Hyperf\Memory;
use Hyperf\Server\SwooleEvent;

class ServerStartCallback
{
    public function beforeStart()
    {
        Memory\LockManager::initialize(SwooleEvent::ON_WORKER_START, SWOOLE_RWLOCK, 'workerStart');
        Memory\AtomicManager::initialize(SwooleEvent::ON_WORKER_START);
    }
}
