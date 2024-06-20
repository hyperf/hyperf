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

namespace Hyperf\RpcClient\Exception;

use JetBrains\PhpStorm\ArrayShape;
use RuntimeException;

class RequestException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        #[ArrayShape([
            'class' => 'string', // The exception class name
            'code' => 'int', // The exception code
            'message' => 'string', // The exception message
            'attributes' => [
                'message' => 'string', // The exception message
                'code' => 'int', // The exception code
                'file' => 'string', // The file path which the exception occurred
                'line' => 'int', // The line of file which the exception occurred
            ],
        ])]
        protected array $throwable = []
    ) {
        parent::__construct($message, $code);
    }

    public function getThrowable(): array
    {
        return $this->throwable;
    }

    public function getThrowableCode(): int
    {
        return intval($this->throwable['code'] ?? $this->throwable['attributes']['code'] ?? 0);
    }

    public function getThrowableMessage(): string
    {
        return strval($this->throwable['message'] ?? $this->throwable['attributes']['message'] ?? '');
    }

    public function getThrowableClassName(): string
    {
        return strval($this->throwable['class']);
    }
}
