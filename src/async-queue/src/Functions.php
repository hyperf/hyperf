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

namespace Hyperf\AsyncQueue;

/**
 * Do not assign a value to the return value of this function unless you are very clear about the consequences of doing so.
 */
function dispatch(JobInterface $job): PendingDispatch
{
    return new PendingDispatch($job);
}
