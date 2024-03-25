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

namespace Hyperf\Coroutine;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Throwable;

class Coroutine
{
    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        return Co::id();
    }

    public static function defer(callable $callable): void
    {
        Co::defer(static function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $throwable) {
                static::printLog($throwable);
            }
        });
    }

    public static function sleep(float $seconds): void
    {
        usleep(intval($seconds * 1000 * 1000));
    }

    /**
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     * @throws RunningInNonCoroutineException when running in non-coroutine context
     * @throws CoroutineDestroyedException when the coroutine has been destroyed
     */
    public static function parentId(?int $coroutineId = null): int
    {
        return Co::pid($coroutineId);
    }

    /**
     * The alias of Coroutine::parentId().
     * @throws CoroutineDestroyedException when running in non-coroutine context
     * @throws RunningInNonCoroutineException when the coroutine has been destroyed
     */
    public static function pid(?int $coroutineId = null): int
    {
        return Co::pid($coroutineId);
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public static function create(callable $callable): int
    {
        $coroutine = Co::create(static function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $throwable) {
                static::printLog($throwable);
            }
        });

        try {
            return $coroutine->getId();
        } catch (Throwable) {
            return -1;
        }
    }

    /**
     * Create a coroutine with a copy of the parent coroutine context.
     */
    public static function fork(callable $callable, array $keys = []): int
    {
        $cid = static::id();
        $callable = static function () use ($callable, $cid, $keys) {
            Context::copy($cid, $keys);
            $callable();
        };

        return static::create($callable);
    }

    public static function inCoroutine(): bool
    {
        return Co::id() > 0;
    }

    public static function stats(): array
    {
        return Co::stats();
    }

    public static function exists(int $id): bool
    {
        return Co::exists($id);
    }

    private static function printLog(Throwable $throwable): void
    {
        if (ApplicationContext::hasContainer()) {
            $container = ApplicationContext::getContainer();
            if ($container->has(StdoutLoggerInterface::class)) {
                $logger = $container->get(StdoutLoggerInterface::class);
                if ($container->has(FormatterInterface::class)) {
                    $formatter = $container->get(FormatterInterface::class);
                    $logger->warning($formatter->format($throwable));
                } else {
                    $logger->warning((string) $throwable);
                }
            }
        }
    }
}
