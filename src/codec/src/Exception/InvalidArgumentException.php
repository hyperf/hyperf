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

class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * The origin data that caused the exception.
     */
    private mixed $original;

    public function setOriginal(mixed $original): void
    {
        $this->original = $original;
    }

    /**
     * Get the origin data that caused the exception.
     */
    public function getOriginal(): mixed
    {
        return $this->original;
    }
}
