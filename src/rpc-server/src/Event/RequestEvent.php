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

namespace Hyperf\RpcServer\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

abstract class RequestEvent
{
    public function __construct(
        public ?ServerRequestInterface $request,
        public ?ResponseInterface $response,
        public ?Throwable $exception = null,
        public string $serverName = 'rpc'
    ) {
    }

    public function getThrowable(): ?Throwable
    {
        return $this->exception;
    }
}
