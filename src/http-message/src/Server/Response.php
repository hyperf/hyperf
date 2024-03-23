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

use Hyperf\Engine\Contract\Http\Writable;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Chunk\Chunkable;
use Hyperf\HttpMessage\Server\Chunk\HasChunk;
use Hyperf\HttpMessage\Stream\SwooleStream;

class Response extends \Hyperf\HttpMessage\Base\Response implements Chunkable
{
    use HasChunk;

    protected array $cookies = [];

    protected array $trailers = [];

    protected ?Writable $connection = null;

    /**
     * Returns an instance with body content.
     */
    public function withContent(string $content): static
    {
        $new = clone $this;
        $new->stream = new SwooleStream($content);
        return $new;
    }

    /**
     * Returns an instance with specified cookies.
     */
    public function withCookie(Cookie $cookie): static
    {
        $clone = clone $this;
        $clone->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $clone;
    }

    /**
     * Returns an instance with specified cookies.
     */
    public function setCookie(Cookie $cookie): static
    {
        $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $this;
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
    public function withTrailer(string $key, mixed $value): static
    {
        $new = clone $this;
        $new->trailers[$key] = $value;
        return $new;
    }

    /**
     * Retrieves a specified trailer value, returns null if the value does not exists.
     */
    public function getTrailer(string $key): mixed
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

    public function setConnection(Writable $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    public function getConnection(): ?Writable
    {
        return $this->connection;
    }
}
