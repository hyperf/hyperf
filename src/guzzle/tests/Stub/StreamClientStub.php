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

class StreamClientStub extends Client
{
    public array $calls = [];

    public int $errorCode = 0;

    public string $errorMessage = '';

    public function __construct()
    {
        parent::__construct('127.0.0.1', 80, false);
    }

    public function setMethod(string $method): bool
    {
        $this->calls['method'] = $method;
        return true;
    }

    public function setData(mixed $data, ?int $length = null): bool
    {
        $this->calls['data'] = [$data, $length];
        return true;
    }

    public function setHeaders(array $headers): bool
    {
        $this->calls['headers'] = $headers;
        return true;
    }

    public function execute(string $path): bool
    {
        $this->calls['path'] = $path;

        $this->statusCode = 201;
        $this->headers = ['x-test' => ['a', 'b'], 'content-type' => 'text/plain'];
        $this->set_cookie_headers = ['sid=1'];
        $this->body = 'done';
        $this->errCode = $this->errorCode;
        $this->errMsg = $this->errorMessage;

        return true;
    }
}
