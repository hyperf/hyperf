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
use Hyperf\Context\ResponseContext;
use Hyperf\Contract\PackerInterface;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\RpcMultiplex\Contract\HostReaderInterface;
use Hyperf\RpcMultiplex\Contract\HttpMessageBuilderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Swow\Psr7\Message\ResponsePlusInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

class HttpMessageBuilder implements HttpMessageBuilderInterface
{
    public function __construct(protected PackerInterface $packer, protected RpcContext $context, protected HostReaderInterface $hostReader)
    {
    }

    public function buildRequest(array $data, array $config = []): ServerRequestPlusInterface
    {
        $extra = $data[Constant::EXTRA] ?? [];
        $uri = $this->buildUri(
            $data[Constant::PATH] ?? '/',
            $data[Constant::HOST] ?? $this->hostReader->read(),
            $data[Constant::PORT] ?? $config['port'] ?? 80
        );

        $parsedData = $data[Constant::DATA] ?? [];

        $this->context->setData($data[Constant::CONTEXT] ?? []);

        $request = new Request('POST', $uri, ['Content-Type' => 'application/json', ...$extra], new SwooleStream(Json::encode($parsedData)));

        return $request->setParsedBody($parsedData);
    }

    public function buildResponse(ServerRequestInterface $request, array $data): ResponsePlusInterface
    {
        $packed = $this->packer->pack($data);

        return $this->response()->setBody(new SwooleStream($packed));
    }

    public function persistToContext(ResponsePlusInterface $response): ResponsePlusInterface
    {
        return ResponseContext::set($response);
    }

    protected function buildUri($path, $host, $port, $scheme = 'http'): UriInterface
    {
        $uri = "{$scheme}://{$host}:{$port}/" . ltrim($path, '/');

        return new Uri($uri);
    }

    /**
     * Get response instance from context.
     */
    protected function response(): ResponsePlusInterface
    {
        return ResponseContext::get();
    }
}
