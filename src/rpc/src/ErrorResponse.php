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

namespace Hyperf\Rpc;

class ErrorResponse
{
    public function __construct(protected null|int|string $id, protected int $code, protected string $message, protected mixed $exception)
    {
    }

    public function getId(): null|int|string
    {
        return $this->id;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getException(): mixed
    {
        return $this->exception;
    }

    public function setException(mixed $exception): static
    {
        $this->exception = $exception;
        return $this;
    }
}
