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

namespace Hyperf\Retry\Policy;

use Hyperf\Context\ApplicationContext;
use Hyperf\Retry\RetryContext;
use Throwable;

class FallbackRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var callable|string
     */
    private mixed $fallback;

    public function __construct(callable|string $fallback)
    {
        $this->fallback = $fallback;
    }

    public function end(RetryContext &$retryContext): bool
    {
        if (! isset($retryContext['retryExhausted'])) {
            return false;
        }

        $fallback = $this->fallback;
        if (is_string($fallback) && strpos($fallback, '@') > 0) {
            [$class, $method] = explode('@', $fallback);
            $fallback = [$this->getContainer()->get($class), $method];
        }

        if (! is_callable($fallback)) {
            return false;
        }
        $throwable = $retryContext['lastThrowable'] ?? null;
        $retryContext['lastThrowable'] = $retryContext['lastResult'] = null;
        if (isset($retryContext['proceedingJoinPoint'])) {
            $arguments = $retryContext['proceedingJoinPoint']->getArguments();
        } else {
            $arguments = [];
        }

        $arguments[] = $throwable;

        try {
            $retryContext['lastResult'] = call_user_func($fallback, ...$arguments);
        } catch (Throwable $throwable) {
            $retryContext['lastThrowable'] = $throwable;
        }
        return false;
    }

    protected function getContainer()
    {
        return ApplicationContext::getContainer();
    }
}
