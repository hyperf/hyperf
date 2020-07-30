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
namespace HyperfTest\GrpcServer\Stub;

use Hyperf\GrpcServer\Exception\Handler\GrpcExceptionHandler;
use Psr\Http\Message\ResponseInterface;

class GrpcExceptionHandlerStub extends GrpcExceptionHandler
{
    public function transferToResponse($code, $message, ResponseInterface $response): ResponseInterface
    {
        return parent::transferToResponse($code, $message, $response);
    }
}
