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
namespace Hyperf\RpcMultiplex;

use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\Contract\PackerInterface;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\RpcMultiplex\Contract\HttpMessageBuilderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class HttpMessageBuilder implements HttpMessageBuilderInterface
{
    public function __construct(protected PackerInterface $packer, protected RpcContext $context)
    {
    }

    public function buildRequest(array $data): ServerRequestInterface
    {
        $uri = $this->buildUri(
            $data[Constant::PATH] ?? '/',
            $data[Constant::HOST] ?? 'unknown',
            $data[Constant::PORT] ?? 80
        );

        $parsedData = $data[Constant::DATA] ?? [];

        $this->context->setData($data[Constant::CONTEXT] ?? []);

        $request = new Request('POST', $uri, ['Content-Type' => 'application/json'], new SwooleStream(Json::encode($parsedData)));

        return $request->withParsedBody($parsedData);
    }

    public function buildResponse(ServerRequestInterface $request, array $data): ResponseInterface
    {
        $packed = $this->packer->pack($data);

        return $this->response()->withBody(new SwooleStream($packed));
    }

    public function persistToContext(ResponseInterface $response): ResponseInterface
    {
        return Context::set(ResponseInterface::class, $response);
    }

    protected function buildUri($path, $host, $port, $scheme = 'http'): UriInterface
    {
        $uri = "{$scheme}://{$host}:{$port}/" . ltrim($path, '/');

        return new Uri($uri);
    }

    /**
     * Get response instance from context.
     */
    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }
}
