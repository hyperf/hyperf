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

use Hyperf\Contract\PackerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResponseBuilder
{
    const SERVER_ERROR = -32000;

    const INVALID_REQUEST = -32600;

    const METHOD_NOT_FOUND = -32601;

    const INVALID_PARAMS = -32602;

    const INTERNAL_ERROR = -32603;

    const PARSE_ERROR = -32700;

    /**
     * @var \Hyperf\Rpc\Contract\DataFormatterInterface
     */
    protected $dataFormatter;

    /**
     * @var PackerInterface
     */
    protected $packer;

    public function __construct(DataFormatterInterface $dataFormatter, PackerInterface $packer)
    {
        $this->dataFormatter = $dataFormatter;
        $this->packer = $packer;
    }

    public function buildErrorResponse(ServerRequestInterface $request, int $code, \Throwable $error = null): ResponseInterface
    {
        $body = new SwooleStream($this->formatErrorResponse($request, $code, $error));
        return $this->response()->withAddedHeader('content-type', 'application/json')->withBody($body);
    }

    public function buildResponse(ServerRequestInterface $request, $response): ResponseInterface
    {
        $body = new SwooleStream($this->formatResponse($response, $request));
        return $this->response()
            ->withAddedHeader('content-type', 'application/json')
            ->withBody($body);
    }

    public function persistToContext(ResponseInterface $response): ResponseInterface
    {
        return Context::set(ResponseInterface::class, $response);
    }

    protected function formatResponse($response, ServerRequestInterface $request): string
    {
        $response = $this->dataFormatter->formatResponse([$request->getAttribute('request_id'), $response]);
        return $this->packer->pack($response);
    }

    protected function formatErrorResponse(ServerRequestInterface $request, int $code, \Throwable $error = null): string
    {
        [$code, $message] = $this->error($code, $error ? $error->getMessage() : null);
        $response = $this->dataFormatter->formatErrorResponse([$request->getAttribute('request_id'), $code, $message, $error]);
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
