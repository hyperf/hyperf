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

/**
 * @method array getServerParams()
 * @method array getCookieParams()
 * @method ServerRequestInterface withCookieParams(array $cookies)
 * @method method getQueryParams()
 * @method ServerRequestInterface withQueryParams(array $query)
 * @method array getUploadedFiles()
 * @method ServerRequestInterface withUploadedFiles(array $uploadedFiles)
 * @method null|null|object getParsedBody()
 * @method ServerRequestInterface withParsedBody($data)
 * @method array getAttributes()
 * @method mixed getAttribute($name, $default = null)
 * @method ServerRequestInterface withAttribute($name, $value)
 * @method ServerRequestInterface withoutAttribute($name)
 */
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
            $request = $this->getRequest();
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
        return $this->getRequest()->getHeaderLine($key);
    }

    public function __call($name, $arguments)
    {
        $request = $this->getRequest();
        if (! method_exists($request, $name)) {
            throw new \RuntimeException('Method not exist.');
        }
        return $request->$name(...$arguments);
    }

    private function getRequest(): ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }
}
