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
 * Block the current coroutine until the specified identifier is resumed.
 */
function block(float $timeout = -1, string $identifier = Constants::WORKER_EXIT): bool
{
    return CoordinatorManager::until($identifier)->yield($timeout);
}

/**
 * Resume the coroutine that is blocked by the specified identifier.
 */
function resume(string $identifier = Constants::WORKER_EXIT): void
{
    CoordinatorManager::until($identifier)->resume();
}

/**
 * Clear the coroutine that is blocked by the specified identifier.
 */
function clear(string $identifier = Constants::WORKER_EXIT): void
{
    CoordinatorManager::clear($identifier);
}
