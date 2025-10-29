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
use Swoole\Coroutine;
use Swoole\Timer;
use Throwable;

trait RunTestsInCoroutine
{
    protected bool $enableCoroutine = true;

    public function runBare(): void
    {
        if ($this->enableCoroutine && extension_loaded('swoole') && Coroutine::getCid() === -1) {
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
}
