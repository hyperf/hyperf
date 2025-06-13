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

namespace Hyperf\GrpcServer\Exception\Handler;

use Google\Rpc\Status;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Grpc\Parser;
use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcServer\Exception\GrpcException;
use Hyperf\GrpcServer\Exception\GrpcStatusException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class GrpcExceptionHandler extends ExceptionHandler
{
    protected StdoutLoggerInterface $logger;

    protected FormatterInterface $formatter;

    public function __construct(StdoutLoggerInterface $logger, FormatterInterface $formatter)
    {
        $this->logger = $logger;
        $this->formatter = $formatter;
    }

    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        if ($throwable instanceof GrpcStatusException) {
            return $this->transferToStatusResponse($throwable->getStatus(), $response);
        }
        if ($throwable instanceof GrpcException) {
            $this->logger->debug($this->formatter->format($throwable));
            $code = $throwable->getCode();
        } else {
            $this->logger->warning($this->formatter->format($throwable));
            $code = StatusCode::INTERNAL;
        }

        return $this->transferToResponse($code, $throwable->getMessage(), $response);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    /**
     * Transfer the non-standard response content to a standard response object.
     */
    protected function transferToResponse(int $code, string $message, ResponsePlusInterface $response): ResponsePlusInterface
    {
        $response = $response->addHeader('Content-Type', 'application/grpc')
            ->addHeader('trailer', 'grpc-status, grpc-message')
            ->setBody(new SwooleStream(Parser::serializeMessage(null)))
            ->setStatus(200);

        if (method_exists($response, 'withTrailer')) {
            $response = $response->withTrailer('grpc-status', (string) $code)->withTrailer('grpc-message', (string) $message);
        }

        return $response;
    }

    /**
     * Transfer the non-standard response content to a standard response object with status trailer.
     */
    protected function transferToStatusResponse(Status $status, ResponsePlusInterface $response): ResponsePlusInterface
    {
        return $response->setStatus(200)
            ->addHeader('Content-Type', 'application/grpc')
            ->addHeader('trailer', 'grpc-status, grpc-message, grpc-status-details-bin')
            ->setBody(new SwooleStream(Parser::serializeMessage(null)))
            ->withTrailer('grpc-status', (string) $status->getCode())
            ->withTrailer('grpc-message', $status->getMessage())
            ->withTrailer('grpc-status-details-bin', Parser::statusToDetailsBin($status));
    }
}
