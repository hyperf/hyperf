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
namespace HyperfTest\Testing\Stub;

use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class FooController
{
    public function index()
    {
        return ['code' => 0, 'data' => 'Hello Hyperf!'];
    }

    public function exception()
    {
        throw new RuntimeException('Server Error', 500);
    }

    public function id()
    {
        return ['code' => 0, 'data' => Coroutine::id()];
    }

    public function context()
    {
        return [
            'request_id' => Context::getOrSet('request_id', uniqid()),
        ];
    }

    public function request()
    {
        /** @var ServerRequestInterface $request */
        $request = Context::get(ServerRequestInterface::class);
        $uri = $request->getUri();
        return [
            'uri' => [
                'scheme' => $uri->getScheme(),
                'host' => $uri->getHost(),
                'port' => $uri->getPort(),
                'path' => $uri->getPath(),
                'query' => $uri->getQuery(),
            ],
            'params' => $request->getQueryParams(),
            'cookies' => $request->getCookieParams(),
        ];
    }
}
