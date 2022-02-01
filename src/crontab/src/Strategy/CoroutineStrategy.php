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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Crontab\Crontab;
use Hyperf\Utils\Coroutine;

class CoroutineStrategy extends AbstractStrategy
{
    public function dispatch(Crontab $crontab)
    {
        Coroutine::create(function () use ($crontab) {
            if ($crontab->getExecuteTime() instanceof Carbon) {
                $wait = $crontab->getExecuteTime()->getTimeStamp() - time();
                if ($wait > 0) {
                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($wait)) {
                        return;
                    }
                }
                $executor = $this->container->get(Executor::class);
                $executor->execute($crontab);
            }
        });
    }
}
