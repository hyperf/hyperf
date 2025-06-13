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

namespace Hyperf\HttpMessage\Cookie;

use Countable;
use Hyperf\Contract\Arrayable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Stores HTTP cookies.
 *
 * It extracts cookies from HTTP requests, and returns them in HTTP responses.
 * CookieJarInterface instances automatically expire contained cookies when
 * necessary. Subclasses are also responsible for storing and retrieving
 * cookies from a file, database, etc.
 *
 * @see http://docs.python.org/2/library/cookielib.html Inspiration
 */
interface CookieJarInterface extends Countable, \IteratorAggregate, Arrayable
{
    /**
     * Create a request with added cookie headers.
     *
     * If no matching cookies are found in the cookie jar, then no Cookie
     * header is added to the request and the same request is returned.
     *
     * @param RequestInterface $request request object to modify
     *
     * @return RequestInterface returns the modified request
     */
    public function withCookieHeader(RequestInterface $request);

    /**
     * Extract cookies from an HTTP response and store them in the CookieJar.
     *
     * @param RequestInterface $request Request that was sent
     * @param ResponseInterface $response Response that was received
     */
    public function extractCookies(
        RequestInterface $request,
        ResponseInterface $response
    );

    /**
     * Sets a cookie in the cookie jar.
     *
     * @param SetCookie $cookie cookie to set
     *
     * @return bool Returns true on success or false on failure
     */
    public function setCookie(SetCookie $cookie);

    /**
     * Remove cookies currently held in the cookie jar.
     *
     * Invoking this method without arguments will empty the whole cookie jar.
     * If given a $domain argument only cookies belonging to that domain will
     * be removed. If given a $domain and $path argument, cookies belonging to
     * the specified path within that domain are removed. If given all three
     * arguments, then the cookie with the specified name, path and domain is
     * removed.
     *
     * @param string $domain Clears cookies matching a domain
     * @param string $path Clears cookies matching a domain and path
     * @param string $name Clears cookies matching a domain, path, and name
     *
     * @return CookieJarInterface
     */
    public function clear($domain = null, $path = null, $name = null);

    /**
     * Discard all sessions cookies.
     *
     * Removes cookies that don't have an expire field or a have a discard
     * field set to true. To be called when the user agent shuts down according
     * to RFC 2965.
     */
    public function clearSessionCookies();
}
