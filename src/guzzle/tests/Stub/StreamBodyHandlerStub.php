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

namespace HyperfTest\Guzzle\Stub;

use Hyperf\Engine\Http\Client;
use Hyperf\Engine\Http\RawResponse;
use Hyperf\Guzzle\CoroutineHandler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class StreamBodyHandlerStub extends CoroutineHandler
{
    public ?array $streamRequest = null;

    public bool $streamBodySupported = true;

    public function createStreamBodySourceForTest(StreamInterface $body): ?array
    {
        return $this->createStreamBodySource($body);
    }

    public function sendRequestForTest(Client $client, RequestInterface $request, string $path, array $headers): RawResponse
    {
        return $this->sendRequest($client, $request, $path, $headers);
    }

    public function requestWithStreamBodyForTest(
        Client $client,
        RequestInterface $request,
        string $path,
        array $headers,
        $resource,
        int $length
    ): RawResponse {
        return parent::requestWithStreamBody($client, $request, $path, $headers, $resource, $length);
    }

    protected function supportsStreamBody(): bool
    {
        return $this->streamBodySupported;
    }

    protected function requestWithStreamBody(
        Client $client,
        RequestInterface $request,
        string $path,
        array $headers,
        $resource,
        int $length
    ): RawResponse {
        $this->streamRequest = [
            'method' => $request->getMethod(),
            'path' => $path,
            'headers' => $headers,
            'resource' => $resource,
            'length' => $length,
            'position' => ftell($resource),
        ];

        return new RawResponse(200, [], 'streamed', '1.1');
    }
}
