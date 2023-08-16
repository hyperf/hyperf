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
namespace Hyperf\JsonRpc;

use Hyperf\Context\Context;
use Hyperf\Contract\PackerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\ErrorResponse;
use Hyperf\Rpc\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ResponseBuilder
{
    public const SERVER_ERROR = -32000;

    public const INVALID_REQUEST = -32600;

    public const METHOD_NOT_FOUND = -32601;

    public const INVALID_PARAMS = -32602;

    public const INTERNAL_ERROR = -32603;

    public const PARSE_ERROR = -32700;

    public function __construct(protected DataFormatterInterface $dataFormatter, protected PackerInterface $packer)
    {
    }

    public function buildErrorResponse(ServerRequestInterface $request, int $code, Throwable $error = null): ResponseInterface
    {
        $body = new SwooleStream($this->formatErrorResponse($request, $code, $error));
        return $this->response()->withHeader('content-type', 'application/json')->withBody($body);
    }

    public function buildResponse(ServerRequestInterface $request, $response): ResponseInterface
    {
        $body = new SwooleStream($this->formatResponse($response, $request));
        return $this->response()
            ->withHeader('content-type', 'application/json')
            ->withBody($body);
    }

    public function persistToContext(ResponseInterface $response): ResponseInterface
    {
        return Context::set(ResponseInterface::class, $response);
    }

    protected function formatResponse($response, ServerRequestInterface $request): string
    {
        $response = $this->dataFormatter->formatResponse(
            new Response($request->getAttribute('request_id'), $response)
        );
        return $this->packer->pack($response);
    }

    protected function formatErrorResponse(ServerRequestInterface $request, int $code, Throwable $error = null): string
    {
        [$code, $message] = $this->error($code, $error?->getMessage());
        $response = $this->dataFormatter->formatErrorResponse(
            new ErrorResponse($request->getAttribute('request_id'), $code, $message, $error)
        );
        return $this->packer->pack($response);
    }

    protected function error(int $code, ?string $message = null): array
    {
        $mapping = [
            self::PARSE_ERROR => 'Parse error.',
            self::INVALID_REQUEST => 'Invalid request.',
            self::METHOD_NOT_FOUND => 'Method not found.',
            self::INVALID_PARAMS => 'Invalid params.',
            self::INTERNAL_ERROR => 'Internal error.',
        ];
        if (isset($mapping[$code])) {
            return [$code, $mapping[$code]];
        }
        return [$code, $message ?? ''];
    }

    /**
     * Get response instance from context.
     */
    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }
}
