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
    public $interval;

    public function run(int $second, callable $handler)
    {
        go(function () use ($second, $handler) {
            retry(INF, function () use ($second, $handler) {
                while (true) {
                    // handler worker exit
                    $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                    $workerExited = $coordinator->yield($second);
                    if ($workerExited) {
                        break;
                    }

                    $handler();
                }
            }, 1000 * $second);
        });
    }
}
