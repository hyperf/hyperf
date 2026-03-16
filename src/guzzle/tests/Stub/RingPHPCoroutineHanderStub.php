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
use Hyperf\Guzzle\RingPHP\CoroutineHandler;
use Mockery;

class RingPHPCoroutineHanderStub extends CoroutineHandler
{
    public $count = 0;

    protected function makeClient(string $host, int $port, bool $ssl): Client
    {
        $client = Mockery::mock(Client::class . '[request]', [$host, $port, $ssl]);
        $client->shouldReceive('request')->withAnyArgs()->andReturnUsing(function ($method, $path, $headers, $body) use ($host, $port, $ssl) {
            ++$this->count;
            $body = json_encode([
                'host' => $host,
                'port' => $port,
                'ssl' => $ssl,
                'method' => $method,
                'headers' => $headers,
                'setting' => [],
                'uri' => $path,
                'body' => $body,
            ]);
            return new RawResponse(200, [], $body, '1.1');
        });
        return $client;
    }
}
