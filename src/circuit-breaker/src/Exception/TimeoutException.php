<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace Hyperf\CircuitBreaker\Exception;

class TimeoutException extends CircuitBreakerException
{
    public function __construct(string $message = '', $result)
    {
        parent::__construct($message);
        $this->result = $result;
    }
}
