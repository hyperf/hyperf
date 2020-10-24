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

class CoroutineHandlerStub extends CoroutineHandler
{
    public $count = 0;

    protected $statusCode;

    public function __construct($statusCode = 200)
    {
        $this->statusCode = $statusCode;
    }

    public function checkStatusCode(Client $client, $request)
    {
        return parent::checkStatusCode($client, $request);
    }

    public function createSink(string $body, string $sink)
    {
        return parent::createSink($body, $sink);
    }

    public function rewriteHeaders(array $headers): array
    {
        return parent::rewriteHeaders($headers);
    }

    protected function makeClient(string $host, int $port, bool $ssl): Client
    {
        $client = \Mockery::mock(Client::class . '[request]', [$host, $port, $ssl]);
        $client->shouldReceive('request')->withAnyArgs()->andReturnUsing(function ($method, $path, $headers, $body) use ($host, $port, $ssl, $client) {
            ++$this->count;
            $body = json_encode([
                'host' => $host,
                'port' => $port,
                'ssl' => $ssl,
                'method' => $method,
                'headers' => $headers,
                'setting' => $client->setting,
                'uri' => $path,
                'body' => $body,
            ]);
            return new RawResponse($this->statusCode, [], $body, '1.1');
        });
        return $client;
    }

    protected function execute(Client $client, string $method, string $path, array $headers, string $body): RawResponse
    {
        ++$this->count;
        $body = json_encode([
            'host' => $client->host,
            'port' => $client->port,
            'ssl' => $client->ssl,
            'setting' => $client->setting,
            'method' => $client->requestMethod,
            'headers' => $client->requestHeaders,
            'uri' => $path,
        ]);
        return new RawResponse($this->statusCode, [], $body, '1.1');
    }
}
