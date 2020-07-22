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
namespace Hyperf\HttpServer;

use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use SplFileInfo;

/**
 * @property string $pathInfo
 * @property string $requestUri
 */
class Request implements RequestInterface
{
    /**
     * @var array the keys to identify the data of request in coroutine context
     */
    protected $contextkeys
        = [
            'parsedData' => 'http.request.parsedData',
        ];

    public function __get($name)
    {
        return $this->getRequestProperty($name);
    }

    public function __set($name, $value)
    {
        return $this->storeRequestProperty($name, $value);
    }

    /**
     * Retrieve the data from query parameters, if $key is null, will return all query parameters.
     *
     * @param mixed $default
     */
    public function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->getQueryParams();
        }
        return data_get($this->getQueryParams(), $key, $default);
    }

    /**
     * Retrieve the data from route parameters.
     *
     * @param mixed $default
     */
    public function route(string $key, $default = null)
    {
        $route = $this->getAttribute(Dispatched::class);
        if (is_null($route)) {
            return $default;
        }
        return array_key_exists($key, $route->params) ? $route->params[$key] : $default;
    }

    /**
     * Retrieve the data from parsed body, if $key is null, will return all parsed body.
     *
     * @param mixed $default
     */
    public function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->getParsedBody();
        }
        return data_get($this->getParsedBody(), $key, $default);
    }

    /**
     * Retrieve the input data from request, include query parameters, parsed body and json body,
     * if $key is null, will return all the parameters.
     *
     * @param mixed $default
     */
    public function input(string $key, $default = null)
    {
        $data = $this->getInputData();

        return data_get($data, $key, $default);
    }

    /**
     * Retrieve the input data from request via multi keys, include query parameters, parsed body and json body.
     *
     * @param mixed $default
     */
    public function inputs(array $keys, $default = null): array
    {
        $data = $this->getInputData();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = data_get($data, $key, $default[$key] ?? null);
        }

        return $result;
    }

    /**
     * Retrieve all input data from request, include query parameters, parsed body and json body.
     */
    public function all(): array
    {
        $data = $this->getInputData();
        return $data ?? [];
    }

    /**
     * Determine if the $keys is exist in parameters.
     *
     * @return array [found, not-found]
     */
    public function hasInput(array $keys): array
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
     * Determine if the $keys is exist in parameters.
     *
     * @param array|string $keys
     */
    public function has($keys): bool
    {
        return Arr::has($this->getInputData(), $keys);
    }

    /**
     * Retrieve the data from request headers.
     *
     * @param mixed $default
     */
    public function header(string $key, $default = null)
    {
        if (! $this->hasHeader($key)) {
            return $default;
        }
        return $this->getHeaderLine($key);
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * Returns the path being requested relative to the executed script.
     * The path info always starts with a /.
     * Suppose this request is instantiated from /mysite on localhost:
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'.
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getPathInfo(): string
    {
        if ($this->pathInfo === null) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo ?? '';
    }

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @param mixed ...$patterns
     */
    public function is(...$patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $this->decodedPath())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the current decoded path info for the request.
     */
    public function decodedPath(): string
    {
        return rawurldecode($this->path());
    }

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri()
    {
        if ($this->requestUri === null) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Get the URL (no query string) for the request.
     */
    public function url(): string
    {
        return rtrim(preg_replace('/\?.*/', '', (string) $this->getUri()), '/');
    }

    /**
     * Get the full URL for the request.
     */
    public function fullUrl(): string
    {
        $query = $this->getQueryString();

        return $this->url() . '?' . $query;
    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return null|string A normalized query string for the Request
     */
    public function getQueryString(): ?string
    {
        $qs = static::normalizeQueryString($this->getServerParams()['query_string'] ?? '');

        return $qs === '' ? null : $qs;
    }

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @param string $qs Query string
     * @return string A normalized query string for the Request
     */
    public function normalizeQueryString(string $qs): string
    {
        if ($qs == '') {
            return '';
        }

        parse_str($qs, $qs);
        ksort($qs);

        return http_build_query($qs, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Retrieve a cookie from the request.
     * @param null|mixed $default
     */
    public function cookie(string $key, $default = null)
    {
        return data_get($this->getCookieParams(), $key, $default);
    }

    /**
     * Determine if a cookie is set on the request.
     */
    public function hasCookie(string $key): bool
    {
        return ! is_null($this->cookie($key));
    }

    /**
     * Retrieve a server variable from the request.
     *
     * @param null|mixed $default
     * @return null|array|string
     */
    public function server(string $key, $default = null)
    {
        return data_get($this->getServerParams(), $key, $default);
    }

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * Retrieve a file from the request.
     *
     * @param null|mixed $default
     * @return null|UploadedFile|UploadedFile[]
     */
    public function file(string $key, $default = null)
    {
        return Arr::get($this->getUploadedFiles(), $key, $default);
    }

    /**
     * Determine if the uploaded data contains a file.
     */
    public function hasFile(string $key): bool
    {
        if ($file = $this->file($key)) {
            return $this->isValidFile($file);
        }
        return false;
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

    /**
     * Check that the given file is a valid SplFileInfo instance.
     * @param mixed $file
     */
    protected function isValidFile($file): bool
    {
        return $file instanceof SplFileInfo && $file->getPath() !== '';
    }

    /**
     * Prepares the path info.
     */
    protected function preparePathInfo(): string
    {
        $requestUri = $this->getRequestUri();

        // Remove the query string from REQUEST_URI
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = '/' . $requestUri;
        }

        return (string) $requestUri;
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */
    protected function prepareRequestUri()
    {
        $requestUri = '';

        $serverParams = $this->getServerParams();
        if (isset($serverParams['request_uri'])) {
            $requestUri = $serverParams['request_uri'];

            if ($requestUri !== '' && $requestUri[0] === '/') {
                // To only use path and query remove the fragment.
                if (false !== $pos = strpos($requestUri, '#')) {
                    $requestUri = substr($requestUri, 0, $pos);
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                $uriComponents = parse_url($requestUri);

                if (isset($uriComponents['path'])) {
                    $requestUri = $uriComponents['path'];
                }

                if (isset($uriComponents['query'])) {
                    $requestUri .= '?' . $uriComponents['query'];
                }
            }
        }

        // normalize the request URI to ease creating sub-requests from this request
        $serverParams['request_uri'] = $requestUri;

        return $requestUri;
    }

    protected function getInputData(): array
    {
        return $this->storeParsedData(function () {
            $request = $this->getRequest();
            if (is_array($request->getParsedBody())) {
                $data = $request->getParsedBody();
            } else {
                $data = [];
            }

            return array_merge($data, $request->getQueryParams());
        });
    }

    protected function storeParsedData(callable $callback)
    {
        if (! Context::has($this->contextkeys['parsedData'])) {
            return Context::set($this->contextkeys['parsedData'], call($callback));
        }
        return Context::get($this->contextkeys['parsedData']);
    }

    protected function storeRequestProperty(string $key, $value): self
    {
        Context::set(__CLASS__ . '.properties.' . $key, value($value));
        return $this;
    }

    protected function getRequestProperty(string $key)
    {
        return Context::get(__CLASS__ . '.properties.' . $key);
    }

    protected function call($name, $arguments)
    {
        $request = $this->getRequest();
        if (! method_exists($request, $name)) {
            throw new \RuntimeException('Method not exist.');
        }
        return $request->{$name}(...$arguments);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }
}
