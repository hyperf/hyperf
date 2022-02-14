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
namespace Hyperf\HttpServer\Contract;

use Hyperf\HttpMessage\Upload\UploadedFile;
use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface extends ServerRequestInterface
{
    /**
     * Retrieve all input data from request, include query parameters, parsed body and json body.
     */
    public function all(): array;

    /**
     * Retrieve the data from query parameters, if $key is null, will return all query parameters.
     * @param mixed $default
     */
    public function query(?string $key = null, $default = null);

    /**
     * Retrieve the data from parsed body, if $key is null, will return all parsed body.
     * @param mixed $default
     */
    public function post(?string $key = null, $default = null);

    /**
     * Retrieve the input data from request, include query parameters, parsed body and json body.
     * @param mixed $default
     */
    public function input(string $key, $default = null);

    /**
     * Retrieve the input data from request via multi keys, include query parameters, parsed body and json body.
     * @param mixed $default
     */
    public function inputs(array $keys, $default = null): array;

    /**
     * Determine if the $keys is exist in parameters.
     * @return array [found, not-found]
     */
    public function hasInput(array $keys): array;

    /**
     * Determine if the $keys is exist in parameters.
     *
     * @param array|string $keys
     */
    public function has($keys): bool;

    /**
     * Retrieve the data from request headers.
     * @param mixed $default
     */
    public function header(string $key, $default = null);

    /**
     * Retrieve the data from route parameters.
     * @param mixed $default
     */
    public function route(string $key, $default = null);

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
    public function getPathInfo(): string;

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @param mixed ...$patterns
     */
    public function is(...$patterns): bool;

    /**
     * Get the current decoded path info for the request.
     */
    public function decodedPath(): string;

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri();

    /**
     * Get the URL (no query string) for the request.
     */
    public function url(): string;

    /**
     * Get the full URL for the request.
     */
    public function fullUrl(): string;

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return null|string A normalized query string for the Request
     */
    public function getQueryString(): ?string;

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @param string $qs Query string
     * @return string A normalized query string for the Request
     */
    public function normalizeQueryString(string $qs): string;

    /**
     * Retrieve a cookie from the request.
     * @param null|mixed $default
     */
    public function cookie(string $key, $default = null);

    /**
     * Determine if a cookie is set on the request.
     */
    public function hasCookie(string $key): bool;

    /**
     * Retrieve a server variable from the request.
     *
     * @param null|mixed $default
     * @return null|array|string
     */
    public function server(string $key, $default = null);

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     */
    public function isMethod(string $method): bool;

    /**
     * Retrieve a file from the request.
     *
     * @param null|mixed $default
     * @return null|UploadedFile|UploadedFile[]
     */
    public function file(string $key, $default = null);

    /**
     * Determine if the uploaded data contains a file.
     */
    public function hasFile(string $key): bool;
}
