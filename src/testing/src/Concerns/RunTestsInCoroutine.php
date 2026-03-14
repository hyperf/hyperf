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

namespace Hyperf\Testing\Concerns;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Testing\Attributes\NonCoroutine;
use ReflectionClass;
use Swoole\Coroutine;
use Swoole\Timer;
use Throwable;

trait RunTestsInCoroutine
{
    public function runBare(): void
    {
        if ($this->isCoroutineEnabled()) {
            $exception = null;

            /* @phpstan-ignore-next-line */
            \Swoole\Coroutine\run(function () use (&$exception) {
                try {
                    parent::runBare();
                } catch (Throwable $e) {
                    $exception = $e;
                } finally {
                    Timer::clearAll();
                    CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
                }
            });

            if ($exception) {
                throw $exception;
            }

            return;
        }

        parent::runBare();
    }

    private function isCoroutineEnabled(): bool
    {
        if (! extension_loaded('swoole') || Coroutine::getCid() !== -1) {
            return false;
        }

        $refClass = new ReflectionClass(static::class);
        foreach ($refClass->getAttributes(NonCoroutine::class) as $attribute) {
            return false;
        }

        $refMethod = $refClass->getMethod($this->name());
        foreach ($refMethod->getAttributes(NonCoroutine::class) as $attribute) {
            return false;
        }

        return true;
    }
}
