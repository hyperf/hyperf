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

use Hyperf\Coroutine\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;
use Swow\Psr7\Message\ResponsePlusInterface;

class ResponsePlusProxy implements ResponsePlusInterface, Stringable
{
    public function __construct(protected ResponseInterface $response)
    {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'with')) {
            return new static($this->response->{$name}(...$arguments));
        }

        if (str_starts_with($name, 'get')) {
            return $this->response->{$name}(...$arguments);
        }

        if (str_starts_with($name, 'set')) {
            $this->response->{$name}(...$arguments);
            return $this;
        }

        throw new InvalidArgumentException(sprintf('The method %s is not supported.', $name));
    }

    public function getCookies()
    {
        if (method_exists($this->response, 'getCookies')) {
            return $this->response->getCookies();
        }

        return [];
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function setProtocolVersion(string $version): static
    {
        $this->response = $this->response->withProtocolVersion($version);
        return $this;
    }

    public function withProtocolVersion(mixed $version): static
    {
        return new static($this->response->withProtocolVersion($version));
    }

    public function hasHeader(mixed $name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(mixed $name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine(mixed $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function setHeader(string $name, mixed $value): static
    {
        $this->response = $this->response->withHeader($name, $value);
        return $this;
    }

    public function withHeader(mixed $name, mixed $value): static
    {
        return new static($this->response->withHeader($name, $value));
    }

    public function addHeader(string $name, mixed $value): static
    {
        $this->response = $this->response->withAddedHeader($name, $value);
        return $this;
    }

    public function withAddedHeader(mixed $name, mixed $value): static
    {
        return new static($this->response->withAddedHeader($name, $value));
    }

    public function unsetHeader(string $name): static
    {
        $this->response = $this->response->withoutHeader($name);
        return $this;
    }

    public function withoutHeader(mixed $name): static
    {
        return new static($this->response->withoutHeader($name));
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function getStandardHeaders(): array
    {
        $headers = $this->getHeaders();
        if (! $this->hasHeader('connection')) {
            $headers['Connection'] = [$this->shouldKeepAlive() ? 'keep-alive' : 'close'];
        }
        if (! $this->hasHeader('content-length')) {
            $headers['Content-Length'] = [(string) ($this->getBody()->getSize() ?? 0)];
        }
        return $headers;
    }

    public function setHeaders(array $headers): static
    {
        foreach ($this->getHeaders() as $key => $value) {
            $this->unsetHeader($key);
        }

        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        return $this;
    }

    public function withHeaders(array $headers): static
    {
        return new static($this->setHeaders($headers)->response);
    }

    public function shouldKeepAlive(): bool
    {
        return strtolower($this->getHeaderLine('Connection')) === 'keep-alive';
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function setBody(StreamInterface $body): static
    {
        $this->response = $this->response->withBody($body);
        return $this;
    }

    public function withBody(StreamInterface $body): static
    {
        return new static($this->response->withBody($body));
    }

    public function toString(bool $withoutBody = false): string
    {
        $headerString = '';
        foreach ($this->getStandardHeaders() as $key => $values) {
            foreach ($values as $value) {
                $headerString .= sprintf("%s: %s\r\n", $key, $value);
            }
        }
        return sprintf(
            "HTTP/%s %s %s\r\n%s\r\n%s",
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase(),
            $headerString,
            $withoutBody ? '' : $this->getBody()
        );
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function setStatus(int $code, string $reasonPhrase = ''): static
    {
        $this->response = $this->response->withStatus($code, $reasonPhrase);
        return $this;
    }

    public function withStatus($code, $reasonPhrase = ''): static
    {
        return new static($this->response->withStatus($code, $reasonPhrase));
    }
}
