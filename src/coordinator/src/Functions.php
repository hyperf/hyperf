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

namespace Hyperf\Coordinator;

/**
 * @param null|callable(bool $isWorkerExited) $callback
 */
function block(float $timeout = -1, ?callable $callback = null, string $identifier = Constants::WORKER_EXIT): void
{
    $isWorkerExited = CoordinatorManager::until($identifier)->yield($timeout);

    if ($callback) {
        $callback($isWorkerExited);
    }
}

function resume(string $identifier = Constants::WORKER_EXIT): void
{
    CoordinatorManager::until($identifier)->resume();
}
