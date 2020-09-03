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
namespace Hyperf\HttpMessage\Exception;

class UnsupportedMediaTypeHttpException extends HttpException
{
    public function __construct($message = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(415, $message, $code, $previous);
    }
}
