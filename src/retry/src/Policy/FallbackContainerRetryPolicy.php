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

use Hyperf\Retry\RetryContext;
use Hyperf\Utils\ApplicationContext;
use Throwable;

class FallbackContainerRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var string
     */
    public $fallbackClass = '';

    /**
     * @var string
     */
    public $fallbackMethod = '';

    public function __construct(string $fallbackClass = '', string $fallbackMethod = '')
    {
        $this->fallbackClass = $fallbackClass;
        $this->fallbackMethod = $fallbackMethod;
    }

    public function end(RetryContext &$retryContext): bool
    {
        if (! isset($retryContext['retryExhausted']) ||
            empty($this->fallbackClass) || empty($this->fallbackMethod)) {
            return false;
        }

        $retryContext['lastThrowable'] = $retryContext['lastResult'] = null;

        $arguments = [];
        if (isset($retryContext['proceedingJoinPoint'])) {
            $arguments = $retryContext['proceedingJoinPoint']->getArguments();
        }

        try {
            $retryContext['lastResult'] = ApplicationContext::getContainer()->get($this->fallbackClass)->{$this->fallbackMethod}(...$arguments);
        } catch (Throwable $throwable) {
            $retryContext['lastThrowable'] = $throwable;
        }

        return false;
    }
}
