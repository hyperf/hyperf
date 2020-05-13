<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Utils\Coroutine;

use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;

class Timer
{
    /**
     * @param int $ms millisecond
     * @param callable $handler
     */
    public function run(int $ms, callable $handler)
    {
        go(function () use ($ms, $handler) {
            retry(INF, function () use ($ms, $handler) {
                while (true) {
                    // handler worker exit
                    $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                    $workerExited = $coordinator->yield($ms);
                    if ($workerExited) {
                        break;
                    }

                    $handler();
                }
            }, $ms);
        });
    }
}
