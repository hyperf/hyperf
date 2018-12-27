<?php

namespace Hyperflex\HttpServer\Exception\Handler;


use Hyperflex\Framework\ExceptionHandler;
use Hyperflex\HttpServer\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Swoft\Http\Message\Stream\SwooleStream;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{

    /**
     * Handle the exception, and return the specified result.
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        return $response->withStatus($throwable->getCode())->withBody(new SwooleStream($throwable->getMessage()));
    }

    /**
     * Determine if the current exception handler should handle the exception,
     *
     * @return bool
     *         If return true, then this exception handler will handle the exception,
     *         If return false, then delegate to next handler.
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}