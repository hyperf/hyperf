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
use Hyperf\HttpMessage\Stream\SwooleStream;

class Response extends \Hyperf\HttpMessage\Base\Response
{
    /**
     * @var array
     */
    protected $cookies = [];

    /**
     * @var array
     */
    protected $trailers = [];

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
     * Returns an instance with specified cookies.
     */
    public function withCookie(Cookie $cookie): self
    {
        $clone = clone $this;
        $clone->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $clone;
    }

    /**
     * Retrieves all cookies.
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Returns an instance with specified trailer.
     * @param string $value
     */
    public function withTrailer(string $key, $value): self
    {
        $new = clone $this;
        $new->trailers[$key] = $value;
        return $new;
    }

    /**
     * Retrieves a specified trailer value, returns null if the value does not exists.
     */
    public function getTrailer(string $key)
    {
        return $this->trailers[$key] ?? null;
    }

    /**
     * Retrieves all trailers values.
     */
    public function getTrailers(): array
    {
        return $this->trailers;
    }
}
