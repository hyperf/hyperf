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
namespace Hyperf\Codec\Exception;

use InvalidArgumentException;
use Throwable;

class InvalidJsonException extends InvalidArgumentException
{
    /**
     * The origin data that caused the exception.
     */
    private mixed $originData = null;

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, mixed $originData = null)
    {
        $this->originData = $originData;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the origin data that caused the exception.
     */
    public function getOriginData(): mixed
    {
        return $this->originData;
    }
}
