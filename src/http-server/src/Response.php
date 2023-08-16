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

use BadMethodCallException;
use Hyperf\Codec\Json;
use Hyperf\Codec\Xml;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\Contract\Xmlable;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Chunk\Chunkable;
use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\HttpServer\Exception\Http\FileException;
use Hyperf\HttpServer\Exception\Http\InvalidResponseException;
use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;
use Hyperf\Support\ClearStatCache;
use Hyperf\Support\MimeTypeExtensionGuesser;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use SplFileInfo;
use Stringable;
use Throwable;

use function get_class;
use function Hyperf\Support\value;

class Response implements PsrResponseInterface, ResponseInterface
{
    use Macroable;

    protected ?PsrResponseInterface $response = null;

    public function __construct(?PsrResponseInterface $response = null)
    {
        $this->response = $response;
    }

    public function __call($method, $parameters)
    {
        $response = $this->getResponse();
        if (! method_exists($response, $method)) {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $method));
        }
        return $response->{$method}(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        $response = Context::get(PsrResponseInterface::class);
        if (! method_exists($response, $method)) {
            throw new BadMethodCallException(sprintf('Call to undefined static method %s::%s()', self::class, $method));
        }
        return $response::{$method}(...$parameters);
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
     * @param mixed|Stringable $data will transfer to a string value
     */
    public function raw($data): PsrResponseInterface
    {
        return $this->getResponse()
            ->withAddedHeader('content-type', 'text/plain; charset=utf-8')
            ->withBody(new SwooleStream((string) $data));
    }

    /**
     * Redirect to an url with a status.
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
            return $schema . '://' . $host . (Str::startsWith($toUrl, '/') ? $toUrl : '/' . $toUrl);
        });
        return $this->getResponse()->withStatus($status)->withAddedHeader('Location', $toUrl);
    }

    /**
     * Create a file download response.
     *
     * @param string $file the file path which want to send to client
     * @param string $name the alias name of the file that client receive
     */
    public function download(string $file, string $name = ''): PsrResponseInterface
    {
        $file = new SplFileInfo($file);

        if (! $file->isReadable()) {
            throw new FileException('File must be readable.');
        }

        $filename = $name ?: $file->getBasename();
        $etag = $this->createEtag($file);
        $contentType = value(function () use ($file) {
            $mineType = null;
            if (ApplicationContext::hasContainer()) {
                $guesser = ApplicationContext::getContainer()->get(MimeTypeExtensionGuesser::class);
                $mineType = $guesser->guessMimeType($file->getExtension());
            }
            return $mineType ?? 'application/octet-stream';
        });

        // Determine if ETag the client expects matches calculated ETag
        $request = Context::get(ServerRequestInterface::class);
        if ($request instanceof ServerRequestInterface) {
            $ifMatch = $request->getHeaderLine('if-match');
            $ifNoneMatch = $request->getHeaderLine('if-none-match');
            $clientEtags = explode(',', $ifMatch ?: $ifNoneMatch);
            /* @phpstan-ignore-next-line */
            array_walk($clientEtags, 'trim');
            if (in_array($etag, $clientEtags, true)) {
                return $this->withStatus(304)->withAddedHeader('content-type', $contentType);
            }
        }

        return $this->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', $contentType)
            ->withHeader('content-disposition', "attachment; filename={$filename}; filename*=UTF-8''" . rawurlencode($filename))
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withHeader('etag', $etag)
            ->withBody(new SwooleFileStream($file));
    }

    public function withCookie(Cookie $cookie): ResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
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
     * @return PsrResponseInterface
     */
    public function withProtocolVersion($version): MessageInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
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
     * This method returns all the header values of the given
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
     * @return PsrResponseInterface
     * @throws InvalidArgumentException for invalid header names or values
     */
    public function withHeader($name, $value): MessageInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
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
     * @return PsrResponseInterface
     * @throws InvalidArgumentException for invalid header names or values
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * Return an instance without the specified header.
     * Header resolution MUST be done without case-sensitivity.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name case-insensitive header field name to remove
     * @return PsrResponseInterface
     */
    public function withoutHeader($name): MessageInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
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
     * @return PsrResponseInterface
     * @throws InvalidArgumentException when the body is not valid
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
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
     * @throws InvalidArgumentException for invalid status code arguments
     */
    public function withStatus($code, $reasonPhrase = ''): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
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

    public function write(string $data): bool
    {
        $response = $this->getResponse();
        if ($response instanceof Chunkable) {
            return $response->write($data);
        }

        return false;
    }

    protected function call($name, $arguments)
    {
        $response = $this->getResponse();

        if (! $response instanceof PsrResponseInterface) {
            throw new InvalidResponseException('The response is not instanceof ' . PsrResponseInterface::class);
        }

        if (! method_exists($response, $name)) {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $name));
        }

        return new static($response->{$name}(...$arguments));
    }

    /**
     * Get ETag header according to the checksum of the file.
     */
    protected function createEtag(SplFileInfo $file, bool $weak = false): string
    {
        $etag = '';
        if ($weak) {
            ClearStatCache::clear($file->getPathname());
            $lastModified = $file->getMTime();
            $filesize = $file->getSize();
            if (! $lastModified || ! $filesize) {
                return $etag;
            }
            $etag = sprintf('W/"%x-%x"', $lastModified, $filesize);
        } else {
            $etag = md5_file($file->getPathname());
        }
        return $etag;
    }

    /**
     * @param array|Arrayable|Jsonable $data
     * @throws EncodingException when the data encoding error
     */
    protected function toJson($data): string
    {
        try {
            $result = Json::encode($data);
        } catch (Throwable $exception) {
            throw new EncodingException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return $result;
    }

    /**
     * @param array|Arrayable|Xmlable $data
     * @param null|mixed $parentNode
     * @param mixed $root
     * @throws EncodingException when the data encoding error
     */
    protected function toXml($data, $parentNode = null, $root = 'root'): string
    {
        return Xml::toXml($data, $parentNode, $root);
    }

    /**
     * Get the response object from context.
     *
     * @return object|PsrResponseInterface it's an object that implemented PsrResponseInterface, or maybe it's a proxy class
     */
    protected function getResponse()
    {
        if ($this->response instanceof PsrResponseInterface) {
            return $this->response;
        }

        return Context::get(PsrResponseInterface::class);
    }
}
