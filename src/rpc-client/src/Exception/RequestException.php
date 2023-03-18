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

use RuntimeException;

class RequestException extends RuntimeException
{
    /**
     * @param $throwable
     * [
     *     'class' => 'RuntimeException', // The exception class name
     *     'code' => 0, // The exception code
     *     'message' => '', // The exception message
     *     'attributes' => [
     *         'message' => '', // The exception message
     *         'code' => 0, // The exception code
     *         'file' => '/opt/www/hyperf/app/JsonRpc/CalculatorService.php', // The file path which the exception occurred
     *         'line' => 99, // The line of file which the exception occurred
     *     ],
     * ]
     * @param string $message
     * @param int $code
     */
    public function __construct($message = '', $code = 0, protected array $throwable = [])
    {
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
