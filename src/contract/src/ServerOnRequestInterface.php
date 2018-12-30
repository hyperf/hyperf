<?php

namespace Hyperf\Contract;

use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\Framework\ExceptionHandlerDispatcher;
use Hyperf\HttpServer\Exception\HttpException;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoft\Http\Message\Server\Request as Psr7Request;
use Swoft\Http\Message\Server\Response as Psr7Response;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Throwable;

interface ServerOnRequestInterface
{
    public function initCoreMiddleware(string $serverName): void;

    public function onRequest(SwooleRequest $request, SwooleResponse $response): void;
}