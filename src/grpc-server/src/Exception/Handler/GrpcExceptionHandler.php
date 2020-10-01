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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcServer\Exception\GrpcException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class GrpcExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    public function __construct(StdoutLoggerInterface $logger, FormatterInterface $formatter)
    {
        $this->logger = $logger;
        $this->formatter = $formatter;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof GrpcException) {
            $this->logger->debug($this->formatter->format($throwable));
        } else {
            $this->logger->warning($this->formatter->format($throwable));
        }

        return $this->transferToResponse($throwable->getCode(), $throwable->getMessage(), $response);
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
            ->withStatus(StatusCode::HTTP_CODE_MAPPING[$code] ?? 500);

        if (method_exists($response, 'withTrailer')) {
            $response = $response->withTrailer('grpc-status', (string) $code)->withTrailer('grpc-message', (string) $message);
        }

        return $response;
    }
}
