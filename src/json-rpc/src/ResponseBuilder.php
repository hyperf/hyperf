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

namespace Hyperf\JsonRpc;

use Hyperf\Contract\PackerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResponseBuilder
{
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

    public function buildErrorResponse(ServerRequestInterface $request, int $code): ResponseInterface
    {
        $body = new SwooleStream($this->formatErrorResponse($request, $code));
        return $this->response()->withAddedHeader('content-type', 'application/json')->withBody($body);
    }

    public function buildResponse(ServerRequestInterface $request, $response): ResponseInterface
    {
        $body = new SwooleStream($this->formatResponse($response, $request));
        return $this->response()
            ->withAddedHeader('content-type', 'application/json')
            ->withBody($body);
    }

    protected function formatResponse($response, ServerRequestInterface $request): string
    {
        $response = $this->dataFormatter->formatResponse([$request->getAttribute('request_id') ?? '', $response]);
        return $this->packer->pack($response);
    }

    protected function formatErrorResponse(ServerRequestInterface $request, int $code): string
    {
        [$code, $message] = $this->error($code);
        $response = $this->dataFormatter->formatErrorResponse([$request->getAttribute('request_id') ?? '', $code, $message, null]);
        return $this->packer->pack($response);
    }

    protected function error(int $code, ?string $message = null): array
    {
        $mapping = [
            -32700 => 'Parse error.',
            -32600 => 'Invalid request.',
            -32601 => 'Method not found.',
            -32602 => 'Invalid params.',
            -32603 => 'Internal error.',
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
