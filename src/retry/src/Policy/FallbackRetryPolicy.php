<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Retry\Policy;

use Hyperf\Retry\RetryContext;

class FallbackRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var callable|string
     */
    private $fallback;

    public function __construct($fallback)
    {
        $this->fallback = $fallback;
    }

    public function end(RetryContext &$retryContext): bool
    {
        if (! isset($retryContext['retryExhausted'])) {
            return false;
        }
        if (! is_callable($this->fallback)) {
            return false;
        }
        $retryContext['lastThrowable'] = $retryContext['lastResult'] = null;
        if (isset($retryContext['proceedingJoinPoint'])) {
            $arguments = $retryContext['proceedingJoinPoint']->getArguments();
        } else {
            $arguments = [];
        }

        try {
            $retryContext['lastResult'] = call_user_func($this->fallback, ...$arguments);
        } catch (\Throwable $throwable) {
            $retryContext['lastThrowable'] = $throwable;
        }
        return false;
    }
}
