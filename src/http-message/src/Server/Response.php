<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpMessage\Server;

use Hyperf\Contract\Sendable;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Stream\FileInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;

class Response extends \Hyperf\HttpMessage\Base\Response implements Sendable
{
    /**
     * @var null|\Swoole\Http\Response
     */
    protected $swooleResponse;

    /**
     * @var array
     */
    protected $cookies = [];

    public function __construct(\Swoole\Http\Response $response = null)
    {
        $this->swooleResponse = $response;
    }

    /**
     * Handle response and send.
     */
    public function send(bool $withContent = true)
    {
        if (! $this->getSwooleResponse()) {
            return;
        }

        $this->buildSwooleResponse($this->swooleResponse, $this);
        $content = $this->getBody();
        if ($content instanceof FileInterface) {
            return $this->swooleResponse->sendfile($content->getFilename());
        }
        if ($withContent) {
            $this->swooleResponse->end($content->getContents());
        } else {
            $this->swooleResponse->end();
        }
    }

    /**
     * Returns an instance with body content.
     */
    public function withContent(string $content): self
    {
        $new = clone $this;
        $new->stream = new SwooleStream($content);
        return $new;
    }

    /**
     * Return an instance with specified cookies.
     */
    public function withCookie(Cookie $cookie): self
    {
        $clone = clone $this;
        $clone->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $clone;
    }

    /**
     * Return all cookies.
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getSwooleResponse(): ?\Swoole\Http\Response
    {
        return $this->swooleResponse;
    }

    public function setSwooleResponse(\Swoole\Http\Response $swooleResponse): self
    {
        $this->swooleResponse = $swooleResponse;
        return $this;
    }

    /**
     * Keep this method at public level,
     * allows the proxy class to override this method,
     * or override the method that used this method.
     */
    public function buildSwooleResponse(\Swoole\Http\Response $swooleResponse, Response $response): void
    {
        /*
         * Headers
         */
        foreach ($response->getHeaders() as $key => $value) {
            $swooleResponse->header($key, implode(';', $value));
        }

        /*
         * Cookies
         */
        foreach ((array) $this->cookies as $domain => $paths) {
            foreach ($paths ?? [] as $path => $item) {
                foreach ($item ?? [] as $name => $cookie) {
                    if ($cookie instanceof Cookie) {
                        $value = $cookie->isRaw() ? $cookie->getValue() : rawurlencode($cookie->getValue());
                        $swooleResponse->rawcookie($cookie->getName(), $value, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
                    }
                }
            }
        }

        /*
         * Status code
         */
        $swooleResponse->status($response->getStatusCode());
    }
}
