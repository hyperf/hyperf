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
    /**
     * The key to identify the parsed data in coroutine context.
     */
    const CONTEXT_KEY = 'httpRequestParsedData';

    /**
     * Retrieve the data from query parameters, if $key is null, will return all query parameters.
     * @param mixed $default
     */
    public function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->getQueryParams();
        }
        return Arr::get($this->getQueryParams(), $key, $default);
    }

    /**
     * Retrieve the data from parsed body, if $key is null, will return all parsed body.
     * @param mixed $default
     */
    public function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->getParsedBody();
        }
        return Arr::get($this->getParsedBody(), $key, $default);
    }

    /**
     * Retrieve the data from request, include query parameters, parsed body and json body,
     * if $key is null, will return all the parameters.
     * @param mixed $default
     */
    public function input(?string $key = null, $default = null)
    {
        $data = $this->getInputData();

        if ($key === null) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

    /**
     * Retrieve the data from request via multi keys, include query parameters, parsed body and json body.
     * @param mixed $default
     */
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
     * Determine if the $keys is exist in parameters.
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

    /**
     * Retrieve the data from request headers.
     * @param mixed $default
     */
    public function header(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->getRequest()->getHeaders();
        }
        return $this->getRequest()->getHeaderLine($key) ?? $default;
    }

    public function getProtocolVersion()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withProtocolVersion($version)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getHeaders(): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function hasHeader($name)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getHeader($name)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getHeaderLine($name)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withHeader($name, $value)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withAddedHeader($name, $value)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withoutHeader($name)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getBody()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withBody(StreamInterface $body)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getRequestTarget()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withRequestTarget($requestTarget)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getMethod(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withMethod($method)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getUri(): UriInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getServerParams()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getCookieParams()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withCookieParams(array $cookies)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getQueryParams()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withQueryParams(array $query)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getUploadedFiles()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getParsedBody()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withParsedBody($data)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getAttributes()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getAttribute($name, $default = null)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withAttribute($name, $value)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withoutAttribute($name)
    {
        return $this->call(__FUNCTION__, func_get_args());
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

    private function storeParsedData(callable $callback)
    {
        if (! Context::has(self::CONTEXT_KEY)) {
            return Context::set(self::CONTEXT_KEY, call($callback));
        }
        return Context::get(self::CONTEXT_KEY);
    }

    private function call($name, $arguments)
    {
        $request = $this->getRequest();
        if (! method_exists($request, $name)) {
            throw new \RuntimeException('Method not exist.');
        }
        return $request->{$name}(...$arguments);
    }

    private function getRequest(): ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }
}
