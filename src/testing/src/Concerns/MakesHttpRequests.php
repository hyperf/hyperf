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
namespace Hyperf\Testing\Concerns;

use Hyperf\Testing\Http\Client;
use Hyperf\Testing\Http\TestResponse;

use function Hyperf\Support\make;

trait MakesHttpRequests
{
    protected function get($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function post($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function put($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function delete($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function json($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function file($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function doRequest(string $method, ...$args): TestResponse
    {
        return $this->createTestResponse(
            make(Client::class)->{$method}(...$args)
        );
    }

    protected function createTestResponse($response): TestResponse
    {
        return new TestResponse($response);
    }
}
