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
namespace Hyperf\Utils;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine as SwooleCoroutine;
use Throwable;

/**
 * @method static void defer(callable $callable)
 */
class Coroutine
{
    public static function __callStatic($name, $arguments)
    {
        if (! method_exists(SwooleCoroutine::class, $name)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s.', $name));
        }
        return SwooleCoroutine::$name(...$arguments);
    }

    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        return SwooleCoroutine::getCid();
    }

    /**
     * Returns the parent coroutine ID.
     * Returns -1 when running in the top level coroutine.
     * Returns null when running in non-coroutine context.
     *
     * @see https://github.com/swoole/swoole-src/pull/2669/files#diff-3bdf726b0ac53be7e274b60d59e6ec80R940
     */
    public static function parentId(?int $coroutineId = null): ?int
    {
        if ($coroutineId) {
            $cid = SwooleCoroutine::getPcid($coroutineId);
        } else {
            $cid = SwooleCoroutine::getPcid();
        }
        if ($cid === false) {
            return null;
        }

        return $cid;
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public static function create(callable $callable): int
    {
        $result = SwooleCoroutine::create(function () use ($callable) {
            try {
                call($callable);
            } catch (Throwable $throwable) {
                if (ApplicationContext::hasContainer()) {
                    $container = ApplicationContext::getContainer();
                    if ($container->has(StdoutLoggerInterface::class)) {
                        /* @var LoggerInterface $logger */
                        $logger = $container->get(StdoutLoggerInterface::class);
                        /* @var FormatterInterface $formatter */
                        if ($container->has(FormatterInterface::class)) {
                            $formatter = $container->get(FormatterInterface::class);
                            $logger->warning($formatter->format($throwable));
                        } else {
                            $logger->warning(sprintf('Uncaptured exception[%s] detected in %s::%d.', get_class($throwable), $throwable->getFile(), $throwable->getLine()));
                        }
                    }
                }
            }
        });
        return is_int($result) ? $result : -1;
    }

    public static function inCoroutine(): bool
    {
        return Coroutine::id() > 0;
    }
}
