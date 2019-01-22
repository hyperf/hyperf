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

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
    const CONTEXT_KEY = 'httpRequestParsedData';

    public function __call($name, $arguments)
    {
        $request = $this->getRequest();
        if (! method_exists($request, $name)) {
            throw new \RuntimeException('Method not exist.');
        }
        return $request->$name(...$arguments);
    }

    public function input(?string $key = null, $default = null)
    {
        $data = $this->getInputData();

        if (is_null($key)) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

    public function inputs(array $keys, $default = null): array
    {
        $data = $this->getInputData();
        $result = $default ?? [];

        foreach ($keys as $key) {
            $result[$key] = Arr::get($data, $key);
        }

        return $result;
    }

    /**
     * @return []array [found, not-found]
     */
    public function hasInput(array $keys = []): array
    {
        $data = $this->getInputData();
        $found = [];

        foreach ($keys as $key) {
            if (Arr::has($data, $key)) {
                $found[] = $key;
            }
        }

        return [
            $found,
            array_diff($keys, $found),
        ];
    }

    public function header(string $key = null, $default = null)
    {
        return $this->getRequest()->getHeaderLine($key);
    }

    public function getProtocolVersion()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withProtocolVersion($version)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getHeaders()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function hasHeader($name)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getHeader($name)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getHeaderLine($name)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withHeader($name, $value)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withAddedHeader($name, $value)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withoutHeader($name)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getBody()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withBody(StreamInterface $body)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getRequestTarget()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withRequestTarget($requestTarget)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getMethod()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withMethod($method)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getUri()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getServerParams()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getCookieParams()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withCookieParams(array $cookies)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getQueryParams()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withQueryParams(array $query)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getUploadedFiles()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getParsedBody()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withParsedBody($data)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getAttributes()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getAttribute($name, $default = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withAttribute($name, $value)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function withoutAttribute($name)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function getInputData(): array
    {
        return $this->storeParsedData(function () {
            $request = $this->getRequest();
            $contentType = $request->getHeaderLine('Content-Type');
            if ($contentType && Str::startsWith($contentType, 'application/json')) {
                $body = $request->getBody();
                $data = json_decode($body->getContents(), true) ?? [];
            } elseif (is_array($request->getParsedBody())) {
                $data = $request->getParsedBody();
            } else {
                $data = [];
            }

            return array_merge($data, $request->getQueryParams());
        });
    }

    private function getRequest(): ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }

    private function storeParsedData(callable $callback)
    {
        if (! Context::has(self::CONTEXT_KEY)) {
            return Context::set(self::CONTEXT_KEY, call($callback));
        }
        return Context::get(self::CONTEXT_KEY);
    }
}
