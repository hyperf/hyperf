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

    public function end(array &$retryContext): bool
    {
        if (! isset($retryContext['retry_exhausted'])) {
            return false;
        }
        if (! is_callable($this->fallback)) {
            return false;
        }
        $retryContext['last_throwable'] = $retryContext['last_result'] = null;
        if (isset($retryContext['proceeding_join_point'])) {
            $arguments = $retryContext['proceeding_join_point']->getArguments();
        } else {
            $arguments = [];
        }
        
        try {
            $retryContext['last_result'] = call_user_func($this->fallback, ...$arguments);
        } catch (\Throwable $throwable) {
            $retryContext['last_throwable'] = $throwable;
        }
        return false;
    }
}
