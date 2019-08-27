<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GrpcServer\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\GrpcServer\Exception\GrpcException;
use Hyperf\Grpc\StatusCode;
use Hyperf\Server\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class GrpcExceptionHandlerSecondary extends GrpcExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error($this->formatter->format($throwable));

        return $this->transferToResponse(StatusCode::UNKNOWN, 'server error', $response);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
