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

use Hyperf\Guzzle\CoroutineHandler;
use Swoole\Coroutine\Http\Client;

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

    protected function execute(Client $client, $path)
    {
        $client->body = json_encode([
            'host' => $client->host,
            'port' => $client->port,
            'ssl' => $client->ssl,
            'setting' => $client->setting,
            'method' => $client->requestMethod,
            'headers' => $client->requestHeaders,
            'uri' => $path,
        ]);
        $client->statusCode = $this->statusCode;
        $client->headers = [];
        ++$this->count;
    }
}
