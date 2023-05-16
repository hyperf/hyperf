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
namespace HyperfTest\Utils\Stub;

use Exception;
use Throwable;

class StringCodeException extends Exception
{
    public function __construct(string $message = '', mixed $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->code = $code;
    }
}
