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
namespace HyperfTest\HttpServer\Stub;

use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpServer\CoreMiddleware;
use Psr\Http\Message\ResponseInterface;

class CoreMiddlewareStub extends CoreMiddleware
{
    public function parseParameters(string $controller, string $action, array $arguments): array
    {
        return parent::parseParameters($controller, $action, $arguments);
    }

    protected function response(): ResponseInterface
    {
        return new Response();
    }
}
