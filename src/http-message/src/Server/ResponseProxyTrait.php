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

use Hyperf\HttpMessage\Cookie\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

trait ResponseProxyTrait
{
    /**
     * @var null|ResponseInterface
     */
    protected $response;

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        if (! $this->response instanceof ResponseInterface) {
            throw new RuntimeException('response is invalid.');
        }
        return $this->response;
    }

    public function getProtocolVersion()
    {
        return $this->getResponse()->getProtocolVersion();
    }

    public function withProtocolVersion($version)
    {
        $this->setResponse($this->getResponse()->withProtocolVersion($version));
        return $this;
    }

    public function getHeaders()
    {
        return $this->getResponse()->getHeaders();
    }

    public function hasHeader($name)
    {
        return $this->getResponse()->hasHeader($name);
    }

    public function getHeader($name)
    {
        return $this->getResponse()->getHeader($name);
    }

    public function getHeaderLine($name)
    {
        return $this->getResponse()->getHeaderLine($name);
    }

    public function withHeader($name, $value)
    {
        $this->setResponse($this->getResponse()->withHeader($name, $value));
        return $this;
    }

    public function withAddedHeader($name, $value)
    {
        $this->setResponse($this->getResponse()->withAddedHeader($name, $value));
        return $this;
    }

    public function withoutHeader($name)
    {
        $this->setResponse($this->getResponse()->withoutHeader($name));
        return $this;
    }

    public function getBody()
    {
        return $this->getResponse()->getBody();
    }

    public function withBody(StreamInterface $body)
    {
        $this->setResponse($this->getResponse()->withBody($body));
        return $this;
    }

    public function getStatusCode()
    {
        return $this->getResponse()->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->setResponse($this->getResponse()->withStatus($code, $reasonPhrase));
        return $this;
    }

    public function getReasonPhrase()
    {
        return $this->getResponse()->getReasonPhrase();
    }

    /**
     * Returns an instance with specified cookies.
     */
    public function withCookie(Cookie $cookie): self
    {
        $this->setResponse($this->getResponse()->withCookie($cookie));
        return $this;
    }

    /**
     * Retrieves all cookies.
     */
    public function getCookies(): array
    {
        return $this->getResponse()->getCookies();
    }

    /**
     * Returns an instance with specified trailer.
     * @param string $value
     */
    public function withTrailer(string $key, $value): self
    {
        $this->setResponse($this->getResponse()->withTrailer($key, $value));
        return $this;
    }

    /**
     * Retrieves a specified trailer value, returns null if the value does not exists.
     */
    public function getTrailer(string $key)
    {
        return $this->getResponse()->getTrailer($key);
    }

    /**
     * Retrieves all trailers values.
     */
    public function getTrailers(): array
    {
        return $this->getResponse()->getTrailers();
    }
}
