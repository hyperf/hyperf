<?php
/**
 * ValidationExceptionHandler.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019-07-26 17:01
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\Validation;


use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof ValidationException) {
            $data = json_encode([
                'code'    => $throwable->getCode(),
                'message' => $throwable->validator->errors()->first(),
            ], JSON_UNESCAPED_UNICODE);

            $this->stopPropagation();

            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }

        return $response;
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}