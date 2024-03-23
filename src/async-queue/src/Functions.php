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

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Context\ApplicationContext;

function dispatch(JobInterface $job, ?int $delay = null, ?int $maxAttempts = null, ?string $pool = null): bool
{
    if (is_int($maxAttempts)) {
        $job->setMaxAttempts($maxAttempts);
    }

    return ApplicationContext::getContainer()
        ->get(DriverFactory::class)
        ->get($pool ?? 'default')
        ->push($job, $delay ?? 0);
}
