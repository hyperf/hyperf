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
use Psr\Http\Message\ResponseInterface;
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

    public function handle(Throwable $throwable, ResponseInterface $response)
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
    protected function transferToResponse(int $code, string $message, ResponseInterface $response): ResponseInterface
    {
        $response = $response->withAddedHeader('Content-Type', 'application/grpc')
            ->withAddedHeader('trailer', 'grpc-status, grpc-message')
            ->withBody(new SwooleStream(Parser::serializeMessage(null)))
            ->withStatus(200);

        if (method_exists($response, 'withTrailer')) {
            $response = $response->withTrailer('grpc-status', (string) $code)->withTrailer('grpc-message', (string) $message);
        }

        return $response;
    }

    /**
     * Transfer the non-standard response content to a standard response object with status trailer.
     */
    protected function transferToStatusResponse(Status $status, ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(200)
            ->withAddedHeader('Content-Type', 'application/grpc')
            ->withAddedHeader('trailer', 'grpc-status, grpc-message, grpc-status-details-bin')
            ->withBody(new SwooleStream(Parser::serializeMessage(null)))
            ->withTrailer('grpc-status', (string) $status->getCode())
            ->withTrailer('grpc-message', $status->getMessage())
            ->withTrailer('grpc-status-details-bin', Parser::statusToDetailsBin($status));
    }
}
