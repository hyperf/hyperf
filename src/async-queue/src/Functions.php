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
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\DispatcherInterface;
use Throwable;

use function Hyperf\Support\make;

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

function dispatch_sync(JobInterface $job): mixed
{
    $container = ApplicationContext::getContainer();
    $dispatcher = $container->has(DispatcherInterface::class)
        ? $container->get(DispatcherInterface::class)
        : null;
    $message = make(JobMessage::class, [$job]);
    try {
        $dispatcher?->dispatch(new BeforeHandle($message));
        $result = $message->job()->handle();
        $dispatcher?->dispatch(new AfterHandle($message));
    } catch (Throwable $exception) {
        $dispatcher?->dispatch(new FailedHandle($message, $exception));
        return false;
    }

    return $result;
}
