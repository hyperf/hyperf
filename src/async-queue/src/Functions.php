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
    if ($maxAttempts) {
        $job->setMaxAttempts($maxAttempts);
    }

    $pool = $pool ?? (fn () => $this->pool ?? 'default')->call($job); // @phpstan-ignore-line
    $delay = $delay ?? (fn () => $this->delay ?? 0)->call($job); // @phpstan-ignore-line

    return ApplicationContext::getContainer()
        ->get(DriverFactory::class)
        ->get($pool)
        ->push($job, $delay);
}
