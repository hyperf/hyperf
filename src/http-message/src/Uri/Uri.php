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

namespace Hyperf\HttpMessage\Uri;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Stringable;
use Swow\Psr7\Message\UriPlusInterface;

class Uri implements UriInterface, UriPlusInterface, Stringable
{
    /**
     * Absolute http and https URIs require a host per RFC 7230 Section 2.7
     * but in generic URIs the host can be empty. So for http(s) URIs
     * we apply this default host when no host is given yet to form a
     * valid URI.
     */
    public const DEFAULT_HTTP_HOST = 'localhost';

    private static array $defaultPorts = [
        'http' => 80,
        'https' => 443,
    ];

    private static string $charUnreserved = 'a-zA-Z0-9_\-\.~';

    private static string $charSubDelims = '!\$&\'\(\)\*\+,;=';

    private static array $replaceQuery = ['=' => '%3D', '&' => '%26'];

    /**
     * uri scheme.
     */
    private string $scheme = '';

    /**
     * uri user info.
     */
    private string $userInfo = '';

    /**
     * uri host.
     */
    private string $host = '';

    /**
     * uri port.
     */
    private ?int $port = null;

    /**
     * uri path.
     */
    private string $path = '';

    /**
     * uri query string.
     */
    private string $query = '';

    /**
     * uri fragment.
     */
    private string $fragment = '';

    /** @var null|array<string, string> */
    private ?array $queryParams = null;

    /**
     * @param string $uri URI to parse
     */
    public function __construct(string $uri = '')
    {
        if ($uri) {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new InvalidArgumentException("Unable to parse URI: {$uri}");
            }

            $this->applyParts($parts);
        }
    }

    /**
     * Return the string representation as a URI reference.
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     */
    public function __toString(): string
    {
        return self::composeComponents(
            $this->scheme,
            $this->getAuthority(),
            $this->path,
            $this->query,
            $this->fragment
        );
    }

    /**
     * Retrieve the scheme component of the URI.
     * If no scheme is present, this method MUST return an empty string.
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string the URI scheme
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Retrieve the authority component of the URI.
     * If no authority information is present, this method MUST return an empty
     * string.
     * The authority syntax of the URI is:
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string the URI authority, in "[user-info@]host[:port]" format
     */
    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * Retrieve the user information component of the URI.
     * If no user information is present, this method MUST return an empty
     * string.
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string the URI user information, in "username[:password]" format
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * Retrieve the host component of the URI.
     * If no host is present, this method MUST return an empty string.
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string the URI host
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Retrieve the port component of the URI.
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int the URI port
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Retrieve the path component of the URI.
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string the URI path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Retrieve the query string of the URI.
     * If no query string is present, this method MUST return an empty string.
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     * As an example, if a value in a key/value a pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string the URI query string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Retrieve the fragment component of the URI.
     * If no fragment is present, this method MUST return an empty string.
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string the URI fragment
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * Return an instance with the specified scheme.
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme the scheme to use with the new instance
     * @return static a new instance with the specified scheme
     * @throws InvalidArgumentException for invalid or unsupported schemes
     */
    public function withScheme(mixed $scheme): static
    {
        $scheme = $this->filterScheme($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }
        $clone = clone $this;
        $clone->scheme = $scheme;
        // TODO add method
        $clone->removeDefaultPort();
        $clone->validateState();
        return $clone;
    }

    /**
     * Return an instance with the specified user information.
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user the username to use for authority
     * @param null|string $password the password associated with $user
     * @return static a new instance with the specified user information
     */
    public function withUserInfo(mixed $user, mixed $password = null): static
    {
        $info = $user;
        if ($password !== '') {
            $info .= ':' . $password;
        }
        if ($this->userInfo === $info) {
            return $this;
        }
        $clone = clone $this;
        $clone->userInfo = $info;
        $clone->validateState();
        return $clone;
    }

    /**
     * Return an instance with the specified host.
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host the hostname to use with the new instance
     * @return static a new instance with the specified host
     * @throws InvalidArgumentException for invalid hostnames
     */
    public function withHost(mixed $host): static
    {
        $host = $this->filterHost($host);
        if ($this->host === $host) {
            return $this;
        }
        $clone = clone $this;
        $clone->host = $host;
        $clone->validateState();
        return $clone;
    }

    /**
     * Return an instance with the specified port.
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int|string $port the port to use with the new instance; a null value
     *                              removes the port information
     * @return static a new instance with the specified port
     * @throws InvalidArgumentException for invalid ports
     */
    public function withPort($port): static
    {
        $port = $this->filterPort($port);
        if ($this->port === $port) {
            return $this;
        }
        $clone = clone $this;
        $clone->port = $port;
        $clone->validateState();
        return $clone;
    }

    /**
     * Return an instance with the specified path.
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path the path to use with the new instance
     * @return static a new instance with the specified path
     * @throws InvalidArgumentException for invalid paths
     */
    public function withPath(mixed $path): static
    {
        $path = $this->filterPath($path);
        if ($this->path === $path) {
            return $this;
        }
        $clone = clone $this;
        $clone->path = $path;
        $clone->validateState();
        return $clone;
    }

    /**
     * Return an instance with the specified query string.
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query the query string to use with the new instance
     * @return static a new instance with the specified query string
     * @throws InvalidArgumentException for invalid query strings
     */
    public function withQuery(mixed $query): static
    {
        $query = $this->filterQueryAndFragment($query);
        if ($this->query === $query) {
            return $this;
        }
        $clone = clone $this;
        $clone->query = $query;
        return $clone;
    }

    /**
     * Creates a new URI with a specific query string value.
     * Any existing query string values that exactly match the provided key are
     * removed and replaced with the given key value pair.
     * A value of null will set the query string key without a value, e.g. "key"
     * instead of "key=value".
     *
     * @param UriInterface $uri URI to use as a base
     * @param string $key key to set
     * @param null|string $value Value to set
     */
    public static function withQueryValue(UriInterface $uri, string $key, ?string $value): UriInterface
    {
        $current = $uri->getQuery();

        if ($current === '') {
            $result = [];
        } else {
            $decodedKey = rawurldecode($key);
            $result = array_filter(explode('&', $current), function ($part) use ($decodedKey) {
                return rawurldecode(explode('=', $part)[0]) !== $decodedKey;
            });
        }

        // Query string separators ("=", "&") within the key or value need to be encoded
        // (while preventing double-encoding) before setting the query string. All other
        // chars that need percent-encoding will be encoded by withQuery().
        $key = strtr($key, self::$replaceQuery);

        if ($value !== null) {
            $result[] = $key . '=' . strtr($value, self::$replaceQuery);
        } else {
            $result[] = $key;
        }

        return $uri->withQuery(implode('&', $result));
    }

    /**
     * Return an instance with the specified URI fragment.
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment the fragment to use with the new instance
     * @return static a new instance with the specified fragment
     */
    public function withFragment(mixed $fragment): static
    {
        $fragment = $this->filterQueryAndFragment($fragment);
        if ($this->fragment === $fragment) {
            return $this;
        }
        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    /**
     * Composes a URI reference string from its various components.
     * Usually this method does not need to be called manually but instead is used indirectly via
     * `Psr\Http\Message\UriInterface::__toString`.
     * PSR-7 UriInterface treats an empty component the same as a missing component as
     * getQuery(), getFragment() etc. always return a string. This explains the slight
     * difference to RFC 3986 Section 5.3.
     * Another adjustment is that the authority separator is added even when the authority is missing/empty
     * for the "file" scheme. This is because PHP stream functions like `file_get_contents` only work with
     * `file:///myfile` but not with `file:/myfile` although they are equivalent according to RFC 3986. But
     * `file:///` is the more common syntax for the file scheme anyway (Chrome for example redirects to
     * that format).
     *
     * @see https://tools.ietf.org/html/rfc3986#section-5.3
     */
    public static function composeComponents(string $scheme, string $authority, string $path, string $query, string $fragment): string
    {
        $uri = '';
        if ($scheme != '') {
            $uri .= $scheme . ':';
        }
        if ($authority != '' || $scheme === 'file') {
            $uri .= '//' . $authority;
        }
        $uri .= $path;
        if ($query != '') {
            $uri .= '?' . $query;
        }
        if ($fragment != '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * Whether the URI has the default port of the current scheme.
     * `Psr\Http\Message\UriInterface::getPort` may return null or the standard port. This method can be used
     * independently of the implementation.
     */
    public function isDefaultPort(): bool
    {
        return $this->getPort() === null || (isset(self::$defaultPorts[$this->getScheme()]) && $this->getPort() === self::$defaultPorts[$this->getScheme()]);
    }

    /**
     * Get default port of the current scheme.
     */
    public function getDefaultPort(): ?int
    {
        return self::$defaultPorts[$this->getScheme()] ?? null;
    }

    public function setScheme(string $scheme): static
    {
        $scheme = $this->filterScheme($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }
        $this->scheme = $scheme;
        // TODO add method
        $this->removeDefaultPort();
        $this->validateState();
        return $this;
    }

    public function setUserInfo(string $user, string $password = ''): static
    {
        $info = $user;
        if ($password !== '') {
            $info .= ':' . $password;
        }
        $this->userInfo = $info;
        $this->validateState();
        return $this;
    }

    public function setHost(string $host): static
    {
        $this->host = $this->filterHost($host);
        $this->validateState();
        return $this;
    }

    public function setPort(?int $port): static
    {
        $port = $this->filterPort($port);
        if ($this->port === $port) {
            return $this;
        }

        $this->port = $port;
        $this->validateState();
        return $this;
    }

    public function setPath(string $path): static
    {
        $path = $this->filterPath($path);
        if ($this->path === $path) {
            return $this;
        }

        $this->path = $path;
        $this->validateState();
        return $this;
    }

    public function setQuery(string $query): static
    {
        $query = $this->filterQueryAndFragment($query);
        $this->query = $query;
        return $this;
    }

    public function getQueryParams(): array
    {
        if (! isset($this->queryParams)) {
            $query = $this->query;
            if ($query === '') {
                $this->queryParams = [];
            } else {
                parse_str($query, $this->queryParams);
            }
        }

        return $this->queryParams;
    }

    public function setQueryParams(array $queryParams): static
    {
        $this->query = http_build_query($queryParams);
        $this->queryParams = $queryParams;

        return $this;
    }

    public function withQueryParams(array $queryParams): static
    {
        return (clone $this)->setQueryParams($queryParams);
    }

    public function setFragment(string $fragment): static
    {
        $fragment = $this->filterQueryAndFragment($fragment);
        $this->fragment = $fragment;
        return $this;
    }

    public static function build(string $scheme, string $authority, string $path, string $query, string $fragment): string
    {
        $schemeSuffix = $scheme !== '' ? ':' : '';
        $authorityPrefix = $authority !== '' ? '//' : '';
        $pathPrefix = '';
        if ($path !== '' && ! str_starts_with($path, '/') && $authority !== '') {
            // If the path is rootless and an authority is present, the path MUST be prefixed by "/"
            $pathPrefix = '/';
        }
        $queryPrefix = $query !== '' ? '?' : '';
        $fragmentPrefix = $fragment !== '' ? '#' : '';

        return $scheme . $schemeSuffix . $authorityPrefix . $authority . $pathPrefix . $path . $queryPrefix . $query . $fragmentPrefix . $fragment;
    }

    public function toString(): string
    {
        return static::build($this->scheme, $this->getAuthority(), $this->path, $this->query, $this->fragment);
    }

    /**
     * Common state validate method.
     */
    private function validateState(): void
    {
        if ($this->host === '' && ($this->scheme === 'http' || $this->scheme === 'https')) {
            $this->host = self::DEFAULT_HTTP_HOST;
        }
        if ($this->getAuthority() === '') {
            if (str_starts_with($this->path, '//')) {
                throw new InvalidArgumentException('The path of a URI without an authority must not start with two slashes "//"');
            }
            if ($this->scheme === '' && str_contains(explode('/', $this->path, 2)[0], ':')) {
                throw new InvalidArgumentException('A relative URI must not have a path beginning with a segment containing a colon');
            }
        } elseif (isset($this->path[0]) && $this->path[0] !== '/') {
            $this->path = '/' . $this->path;
        }
    }

    /**
     * Apply parse_url parts to a URI.
     *
     * @param array $parts array of parse_url parts to apply
     */
    private function applyParts(array $parts): void
    {
        $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->userInfo = $parts['user'] ?? '';
        $this->host = isset($parts['host']) ? $this->filterHost($parts['host']) : '';
        $this->port = isset($parts['port']) ? $this->filterPort($parts['port']) : null;
        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query = isset($parts['query']) ? $this->filterQueryAndFragment($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->filterQueryAndFragment($parts['fragment']) : '';
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }

        $this->removeDefaultPort();
    }

    private function filterScheme(string $scheme): string
    {
        return strtolower($scheme);
    }

    private function filterHost(string $host): string
    {
        return strtolower($host);
    }

    /**
     * @param null|int|string $port
     */
    private function filterPort($port): ?int
    {
        if ($port === null) {
            return null;
        }

        $port = (int) $port;
        if (1 > $port || 0xFFFF < $port) {
            throw new InvalidArgumentException(sprintf('Invalid port: %d. Must be between 1 and 65535', $port));
        }

        return $port;
    }

    /**
     * Remove the port property when the property is a default port.
     */
    private function removeDefaultPort(): void
    {
        if ($this->port !== null && $this->isDefaultPort()) {
            $this->port = null;
        }
    }

    /**
     * Filters the path of a URI.
     *
     * @throws InvalidArgumentException if the path is invalid
     */
    private function filterPath(string $path): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelims . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            [
                $this,
                'rawurlencodeMatchZero',
            ],
            $path
        );
    }

    /**
     * Filters the query string or fragment of a URI.
     */
    private function filterQueryAndFragment(string $str): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelims . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            [
                $this,
                'rawurlencodeMatchZero',
            ],
            $str
        );
    }

    private function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode($match[0]);
    }
}
