<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use Hyperf\HttpServer\Contract\HttpRequestInterface;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;

class HttpRequest implements HttpRequestInterface
{
    const CONTEXT_KEY = 'httpRequestData';

    public function input(?string $key = null, $default = null)
    {
        $data = [];
        if (Context::has(HttpRequest::CONTEXT_KEY)) {
            $data = Context::get(HttpRequest::CONTEXT_KEY);
        }

        if (empty($data)) {
            /** @var ServerRequestInterface $request */
            $request = Context::get(ServerRequestInterface::class);
            $contentType = $request->getHeaderLine('Content-Type');
            if ($contentType && Str::startsWith($contentType, 'application/json')) {
                $body = $request->getBody();
                $data = json_decode($body->getContents(), true) ?? [];
            } else {
                if (is_array($request->getParsedBody())) {
                    $data = $request->getParsedBody();
                } else {
                    $data = [];
                }
            }

            $data = array_merge($data, $request->getQueryParams());
            Context::set(HttpRequest::CONTEXT_KEY, $data);
        }

        if (is_null($key)) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

    public function header(string $key = null, $default = null)
    {
        /** @var ServerRequestInterface $request */
        $request = Context::get(ServerRequestInterface::class);
        return $request->getHeaderLine($key);
    }
}
