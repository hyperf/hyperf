<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use BadMethodCallException;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Response as ServerResponse;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Exception\HttpException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Contracts\Xmlable;
use Hyperf\Utils\Str;
use Hyperf\Utils\Traits\Macroable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use SimpleXMLElement;
use Swoole\Http\Response as SwooleResponse;
use function get_class;

/**
 * @method void send()
 * @method ServerResponse withContent(string $content)
 * @method ServerResponse withCookie(Cookie $cookie)
 * @method null|SwooleResponse getSwooleResponse()
 * @method ServerResponse setSwooleResponse(SwooleResponse $swooleResponse)
 * @method void buildSwooleResponse(SwooleResponse $swooleResponse, ServerResponse $response)
 */
class Response extends ServerResponse implements ResponseInterface
{
    use Macroable;

    public function __call($name, $arguments)
    {
        $response = $this->getResponse();
        if (! method_exists($response, $name)) {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $name));
        }
        return $response->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $response = Context::get(PsrResponseInterface::class);
        if (! method_exists($response, $name)) {
            throw new BadMethodCallException(sprintf('Call to undefined static method %s::%s()', self::class, $name));
        }
        return $response::{$name}(...$arguments);
    }

    /**
     * Format data to JSON and return data with Content-Type:application/json header.
     *
     * @param array|Arrayable|Jsonable $data
     */
    public function json($data): PsrResponseInterface
    {
        $data = $this->toJson($data);
        return $this->getResponse()
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream($data));
    }

    /**
     * Format data to XML and return data with Content-Type:application/xml header.
     *
     * @param array|Arrayable|Xmlable $data
     */
    public function xml($data, string $root = 'root'): PsrResponseInterface
    {
        $data = $this->toXml($data, null, $root);
        return $this->getResponse()
            ->withAddedHeader('content-type', 'application/xml; charset=utf-8')
            ->withBody(new SwooleStream($data));
    }

    /**
     * Format data to a string and return data with content-type:text/plain header.
     *
     * @param mixed $data will transfer to a string value
     */
    public function raw($data): PsrResponseInterface
    {
        return $this->getResponse()
            ->withAddedHeader('content-type', 'text/plain; charset=utf-8')
            ->withBody(new SwooleStream((string) $data));
    }

    /**
     * Redirect to a url with a status.
     */
    public function redirect(
        string $toUrl,
        int $status = 302,
        string $schema = 'http'
    ): PsrResponseInterface {
        $toUrl = value(function () use ($toUrl, $schema) {
            if (! ApplicationContext::hasContainer() || Str::startsWith($toUrl, ['http://', 'https://'])) {
                return $toUrl;
            }
            /** @var Contract\RequestInterface $request */
            $request = ApplicationContext::getContainer()->get(Contract\RequestInterface::class);
            $uri = $request->getUri();
            $host = $uri->getAuthority();
            // Build the url by $schema and host.
            return $schema . '://' . $host . '/' . $toUrl;
        });
        return $this->getResponse()->withStatus($status)->withAddedHeader('Location', $toUrl);
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version
     */
    public function getProtocolVersion(): string
    {
        return $this->getResponse()->getProtocolVersion();
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        return $this->getResponse()->withProtocolVersion($version);
    }

    /**
     * Retrieves all message header values.
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *                    key MUST be a header name, and each value MUST be an array of strings
     *                    for that header.
     */
    public function getHeaders(): array
    {
        return $this->getResponse()->getHeaders();
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name case-insensitive header field name
     * @return bool Returns true if any header names match the given header
     *              name using a case-insensitive string comparison. Returns false if
     *              no matching header name is found in the message.
     */
    public function hasHeader($name): bool
    {
        return $this->getResponse()->hasHeader($name);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name case-insensitive header field name
     * @return string[] An array of string values as provided for the given
     *                  header. If the header does not appear in the message, this method MUST
     *                  return an empty array.
     */
    public function getHeader($name): array
    {
        return $this->getResponse()->getHeader($name);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name case-insensitive header field name
     * @return string A string of values as provided for the given header
     *                concatenated together using a comma. If the header does not appear in
     *                the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name): string
    {
        return $this->getResponse()->getHeaderLine($name);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name case-insensitive header field name
     * @param string|string[] $value header value(s)
     * @throws \InvalidArgumentException for invalid header names or values
     * @return static
     */
    public function withHeader($name, $value)
    {
        return $this->getResponse()->withHeader($name, $value);
    }

    /**
     * Return an instance with the specified header appended with the given value.
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name case-insensitive header field name to add
     * @param string|string[] $value header value(s)
     * @throws \InvalidArgumentException for invalid header names or values
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        return $this->getResponse()->withAddedHeader($name, $value);
    }

    /**
     * Return an instance without the specified header.
     * Header resolution MUST be done without case-sensitivity.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name case-insensitive header field name to remove
     * @return static
     */
    public function withoutHeader($name)
    {
        return $this->getResponse()->withoutHeader($name);
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface returns the body as a stream
     */
    public function getBody(): StreamInterface
    {
        return $this->getResponse()->getBody();
    }

    /**
     * Return an instance with the specified message body.
     * The body MUST be a StreamInterface object.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body body
     * @throws \InvalidArgumentException when the body is not valid
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        return $this->getResponse()->withBody($body);
    }

    /**
     * Gets the response status code.
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int status code
     */
    public function getStatusCode(): int
    {
        return $this->getResponse()->getStatusCode();
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code the 3-digit integer result code to set
     * @param string $reasonPhrase the reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification
     * @throws \InvalidArgumentException for invalid status code arguments
     * @return static
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->getResponse()->withStatus($code, $reasonPhrase);
    }

    /**
     * Gets the response reason phrase associated with the status code.
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string reason phrase; must return an empty string if none present
     */
    public function getReasonPhrase(): string
    {
        return $this->getResponse()->getReasonPhrase();
    }

    /**
     * @param array|Arrayable|Jsonable $data
     * @throws HttpException when the data encoding error
     */
    protected function toJson($data): string
    {
        if (is_array($data)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            return json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);
        }

        throw new HttpException('Error encoding response data to JSON.');
    }

    /**
     * @param array|Arrayable|Xmlable $data
     * @param null|mixed $parentNode
     * @param mixed $root
     * @throws HttpException when the data encoding error
     */
    protected function toXml($data, $parentNode = null, $root = 'root')
    {
        if ($data instanceof Xmlable) {
            return (string) $data;
        }
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } else {
            $data = (array) $data;
        }
        if ($parentNode === null) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?>' . "<{$root}></{$root}>");
        } else {
            $xml = $parentNode;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->toXml($value, $xml->addChild($key));
            } else {
                if (is_numeric($key)) {
                    $xml->addChild('item' . $key, (string) $value);
                } else {
                    $xml->addChild($key, (string) $value);
                }
            }
        }
        return trim($xml->asXML());
    }

    /**
     * Get the response object from context.
     *
     * @return object|PsrResponseInterface it's an object that implemented PsrResponseInterface, or maybe it's a proxy class
     */
    protected function getResponse()
    {
        return Context::get(PsrResponseInterface::class);
    }
}
