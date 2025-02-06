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

/**
 * @method string name()
 */
trait RunTestsInCoroutine
{
    protected bool $enableCoroutine = true;

    protected string $realTestName = '';

    final protected function runTestsInCoroutine(...$arguments)
    {
        parent::setName($this->realTestName);

        $testResult = null;
        $exception = null;

        /* @phpstan-ignore-next-line */
        \Swoole\Coroutine\run(function () use (&$testResult, &$exception, $arguments) {
            try {
                $this->invokeBeforeHookMethods();
                $testResult = $this->{$this->realTestName}(...$arguments);
            } catch (Throwable $e) {
                $exception = $e;
            } finally {
                $this->invokeAfterHookMethods();
                Timer::clearAll();
                CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
            }
        });

        if ($exception) {
            throw $exception;
        }

        return $testResult;
    }

    final protected function runTest(): mixed
    {
        if (extension_loaded('swoole') && Coroutine::getCid() === -1 && $this->enableCoroutine) {
            $this->realTestName = $this->name();
            parent::setName('runTestsInCoroutine');
        }

        return parent::runTest();
    }

    private function invokeBeforeHookMethods(): void
    {
        if (method_exists($this, 'beforeTestInCoroutine')) {
            call_user_func([$this, 'beforeTestInCoroutine']);
        }
    }

    private function invokeAfterHookMethods(): void
    {
        if (method_exists($this, 'afterTestInCoroutine')) {
            call_user_func([$this, 'afterTestInCoroutine']);
        }
    }
}
