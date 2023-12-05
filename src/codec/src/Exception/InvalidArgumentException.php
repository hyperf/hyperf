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

use Throwable;

class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * The origin data that caused the exception.
     */
    private mixed $original;

    public static function fromPrevious(Throwable|string $exception, mixed $original): self
    {
        $exception = $exception instanceof Throwable ? $exception : new \InvalidArgumentException($exception);
        $e = new static($exception->getMessage(), (int) $exception->getCode(), $exception);
        $e->original = $original;
        return $e;
    }

    /**
     * Get the origin data that caused the exception.
     */
    public function getOriginal(): mixed
    {
        return $this->original;
    }
}
