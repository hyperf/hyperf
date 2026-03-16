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

namespace Hyperf\CircuitBreaker\Exception;

class TimeoutException extends CircuitBreakerException
{
    public function __construct(string $message, $result)
    {
        parent::__construct($message);
        $this->result = $result;
    }
}
