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
namespace Hyperf\HttpMessage\Server;

use Hyperf\HttpMessage\Exception\BadRequestHttpException;
use Hyperf\HttpMessage\Server\Request\Parser;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class Request extends \Hyperf\HttpMessage\Base\Request implements ServerRequestInterface
{
    /**
     * @var \Swoole\Http\Request
     */
    protected $swooleRequest;

    /**
     * @var null|RequestParserInterface
     */
    protected static $parser;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $cookieParams = [];

    /**
     * @var null|array|object
     */
    private $parsedBody;

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var array
     */
    private $serverParams = [];

    /**
     * @var array
     */
    private $uploadedFiles = [];

    /**
     * the body of parser.
     *
     * @var mixed
     */
    private $bodyParams;

    /**
     * Load a swoole request, and transfer to a psr-7 request object.
     *
     * @return \Hyperf\HttpMessage\Server\Request
     */
    public static function loadFromSwooleRequest(\Swoole\Http\Request $swooleRequest)
    {
        $server = $swooleRequest->server;
        $method = $server['request_method'] ?? 'GET';
        $headers = $swooleRequest->header ?? [];
        $uri = self::getUriFromGlobals($swooleRequest);
        $body = new SwooleStream((string) $swooleRequest->rawContent());
        $protocol = isset($server['server_protocol']) ? str_replace('HTTP/', '', $server['server_protocol']) : '1.1';
        $request = new Request($method, $uri, $headers, $body, $protocol);
        $request->cookieParams = ($swooleRequest->cookie ?? []);
        $request->queryParams = ($swooleRequest->get ?? []);
        $request->serverParams = ($server ?? []);
        $request->parsedBody = self::normalizeParsedBody($swooleRequest->post ?? [], $request);
        $request->uploadedFiles = self::normalizeFiles($swooleRequest->files ?? []);
        $request->swooleRequest = $swooleRequest;
        return $request;
    }

    /**
     * Retrieve server parameters.
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Return an instance with the specified server params.
     *
     * @return static
     */
    public function withServerParams(array $serverParams)
    {
        $clone = clone $this;
        $clone->serverParams = $serverParams;
        return $clone;
    }

    /**
     * Retrieve cookies.
     * Retrieves cookies sent by the client to the server.
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * Return an instance with the specified cookies.
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies array of key/value pairs representing cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    /**
     * Retrieve query string arguments.
     * Retrieves the deserialized query string arguments, if any.
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * add param.
     *
     * @param string $name the name of param
     * @param mixed $value the value of param
     *
     * @return static
     */
    public function addQueryParam(string $name, $value)
    {
        $clone = clone $this;
        $clone->queryParams[$name] = $value;

        return $clone;
    }

    /**
     * Return an instance with the specified query string arguments.
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query array of query string arguments, typically from
     *                     $_GET
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * Retrieve normalized file upload data.
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array an array tree of UploadedFileInterface instances; an empty
     *               array MUST be returned if no data is present
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles an array tree of UploadedFileInterface instances
     * @throws \InvalidArgumentException if an invalid structure is provided
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /**
     * Retrieve any parameters provided in the request body.
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *                           These will typically be an array or object.
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * add parser body.
     *
     * @param string $name the name of param
     * @param mixed $value the value of param
     *
     * @return static
     */
    public function addParserBody(string $name, $value)
    {
        if (is_array($this->parsedBody)) {
            $clone = clone $this;
            $clone->parsedBody[$name] = $value;

            return $clone;
        }
        return $this;
    }

    /**
     * return parser result of body.
     *
     * @return mixed
     */
    public function getBodyParams()
    {
        return $this->bodyParams;
    }

    /**
     * Return an instance with the specified body parameters.
     * These MAY be injected during instantiation.
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object.
     * @throws \InvalidArgumentException if an unsupported argument type is
     *                                   provided
     * @return static
     */
    public function withParsedBody($data)
    {
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    /**
     * init body params from parser result.
     *
     * @param mixed $data
     *
     * @return static
     */
    public function withBodyParams($data)
    {
        $clone = clone $this;
        $clone->bodyParams = $data;
        return $clone;
    }

    /**
     * Retrieve attributes derived from the request.
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array attributes derived from the request
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param string $name the attribute name
     * @param mixed $default default value to return if the attribute does not exist
     * @return mixed
     * @see getAttributes()
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param string $name the attribute name
     * @param mixed $value the value of the attribute
     * @return static
     * @see getAttributes()
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @param string $name the attribute name
     * @return static
     * @see getAttributes()
     */
    public function withoutAttribute($name)
    {
        if (array_key_exists($name, $this->attributes) === false) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', (string) $this->getUri()), '/');
    }

    /**
     * Get the full URL for the request.
     */
    public function fullUrl(): string
    {
        $query = $this->getUri()->getQuery();
        $question = $this->getUri()->getHost() . $this->getUri()->getPath() == '/' ? '/?' : '?';
        return $query ? $this->url() . $question . $query : $this->url();
    }

    /**
     * Determine if the request is the result of an ajax call.
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @see http://en.wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool true if the request is an XMLHttpRequest, false otherwise
     */
    public function isXmlHttpRequest()
    {
        return $this->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';
    }

    public function getSwooleRequest(): \Swoole\Http\Request
    {
        return $this->swooleRequest;
    }

    /**
     * @return $this
     */
    public function setSwooleRequest(\Swoole\Http\Request $swooleRequest)
    {
        $this->swooleRequest = $swooleRequest;
        return $this;
    }

    protected static function normalizeParsedBody(array $data = [], ?RequestInterface $request = null)
    {
        if (! $request) {
            return $data;
        }

        $rawContentType = $request->getHeaderLine('content-type');
        if (($pos = strpos($rawContentType, ';')) !== false) {
            // e.g. text/html; charset=UTF-8
            $contentType = strtolower(substr($rawContentType, 0, $pos));
        } else {
            $contentType = strtolower($rawContentType);
        }

        try {
            $parser = static::getParser();
            if ($parser->has($contentType) && $content = $request->getBody()->getContents()) {
                $data = $parser->parse($content, $contentType);
            }
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return $data;
    }

    protected static function getParser(): RequestParserInterface
    {
        if (static::$parser instanceof RequestParserInterface) {
            return static::$parser;
        }

        if (ApplicationContext::hasContainer() && ApplicationContext::getContainer()->has(RequestParserInterface::class)) {
            $parser = ApplicationContext::getContainer()->get(RequestParserInterface::class);
        } else {
            $parser = new Parser();
        }

        return static::$parser = $parser;
    }

    /**
     * Return an UploadedFile instance array.
     *
     * @param array $files A array which respect $_FILES structure
     * @throws \InvalidArgumentException for unrecognized values
     * @return array
     */
    private static function normalizeFiles(array $files)
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
                continue;
            } else {
                throw new BadRequestHttpException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * Create and return an UploadedFile instance from a $_FILES specification.
     * If the specification represents an array of values, this method will
     * delegate to normalizeNestedFileSpec() and return that return value.
     *
     * @param array $value $_FILES struct
     * @return array|UploadedFileInterface
     */
    private static function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }

        return new UploadedFile($value['tmp_name'], (int) $value['size'], (int) $value['error'], $value['name'], $value['type']);
    }

    /**
     * Normalize an array of file specifications.
     * Loops through all nested files and returns a normalized array of
     * UploadedFileInterface instances.
     *
     * @return UploadedFileInterface[]
     */
    private static function normalizeNestedFileSpec(array $files = [])
    {
        $normalizedFiles = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key],
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
            ];
            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }

    /**
     * Get a Uri populated with values from $swooleRequest->server.
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\UriInterface
     */
    private static function getUriFromGlobals(\Swoole\Http\Request $swooleRequest)
    {
        $server = $swooleRequest->server;
        $header = $swooleRequest->header;
        $uri = new Uri();
        $uri = $uri->withScheme(! empty($server['https']) && $server['https'] !== 'off' ? 'https' : 'http');

        $hasPort = false;
        if (isset($server['http_host'])) {
            $hostHeaderParts = explode(':', $server['http_host']);
            $uri = $uri->withHost($hostHeaderParts[0]);
            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri = $uri->withPort($hostHeaderParts[1]);
            }
        } elseif (isset($server['server_name'])) {
            $uri = $uri->withHost($server['server_name']);
        } elseif (isset($server['server_addr'])) {
            $uri = $uri->withHost($server['server_addr']);
        } elseif (isset($header['host'])) {
            $hasPort = true;
            if (\strpos($header['host'], ':')) {
                [$host, $port] = explode(':', $header['host'], 2);
                if ($port != $uri->getDefaultPort()) {
                    $uri = $uri->withPort($port);
                }
            } else {
                $host = $header['host'];
            }

            $uri = $uri->withHost($host);
        }

        if (! $hasPort && isset($server['server_port'])) {
            $uri = $uri->withPort($server['server_port']);
        }

        $hasQuery = false;
        if (isset($server['request_uri'])) {
            $requestUriParts = explode('?', $server['request_uri']);
            $uri = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }

        if (! $hasQuery && isset($server['query_string'])) {
            $uri = $uri->withQuery($server['query_string']);
        }

        return $uri;
    }
}
