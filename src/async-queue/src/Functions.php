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

function dispatch(JobInterface $job, ?string $pool = null, ?int $delay = null, ?int $maxAttempts = null): bool
{
    if (! is_null($maxAttempts)) {
        (function ($maxAttempts) {
            if (property_exists($this, 'maxAttempts')) {
                $this->maxAttempts = $maxAttempts;
            }
        })->call($job, $maxAttempts);
    }

    $pool = $pool ?? (fn () => $this->pool ?? null)->call($job) ?? 'default';
    $delay = $delay ?? (fn () => $this->delay ?? null)->call($job) ?? 0;

    return ApplicationContext::getContainer()
        ->get(DriverFactory::class)
        ->get($pool)
        ->push($job, $delay);
}
