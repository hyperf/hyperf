<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Swoole\Http\Request as SwooleHttpRequest;
use function strlen;
use function substr;
use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

final class SwooleStream implements StreamInterface
{
    /**
     * Memoized body content, as pulled via SwooleHttpRequest::rawContent().
     *
     * @var string
     */
    private $body;

    /**
     * Length of the request body content.
     *
     * @var int
     */
    private $bodySize;

    /**
     * Index to which we have seek'd or read within the request body.
     *
     * @var int
     */
    private $index = 0;

    /**
     * Swoole request containing the body contents.
     */
    private $request;

    public function __construct(SwooleHttpRequest $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $this->body !== null || $this->initRawContent();
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        // If we're at the end of the string, return an empty string.
        if ($this->eof()) {
            return '';
        }
        $size = $this->getSize();
        // If we have not content, return an empty string
        if ($size === 0) {
            return '';
        }
        // Memoize index so we can use it to get a substring later,
        // if required.
        $index = $this->index;
        // Set the internal index to the end of the string
        $this->index = $size - 1;
        if ($index) {
            // Per PSR-7 spec, if we have seeked or read to a given position in
            // the string, we should only return the contents from that position
            // forward.
            return substr($this->body, $index);
        }
        // If we're at the start of the content, return all of it.
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if ($this->bodySize === null) {
            $this->body !== null || $this->initRawContent();
            $this->bodySize = strlen($this->body);
        }
        return $this->bodySize;
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return $this->index >= $this->getSize() - 1;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $this->body !== null || $this->initRawContent();
        $result = substr($this->body, $this->index, $length);
        // Reset index based on legnth; should not be > EOF position.
        $size = $this->getSize();
        $this->index = $this->index + $length >= $size ? $size - 1 : $this->index + $length;
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $size = $this->getSize();
        switch ($whence) {
            case SEEK_SET:
                if ($offset >= $size) {
                    throw new RuntimeException('Offset cannot be longer than content size');
                }
                $this->index = $offset;
                break;
            case SEEK_CUR:
                if ($offset + $this->index >= $size) {
                    throw new RuntimeException('Offset + current position cannot be longer than content size when using SEEK_CUR');
                }
                $this->index += $offset;
                break;
            case SEEK_END:
                if ($offset + $size >= $size) {
                    throw new RuntimeException('Offset must be a negative number to be under the content size when using SEEK_END');
                }
                $this->index = ($size - 1) + $offset;
                break;
            default:
                throw new InvalidArgumentException('Invalid $whence argument provided; must be one of SEEK_CUR,' . 'SEEK_END, or SEEK_SET');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        throw new RuntimeException('Stream is not writable');
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }

    /**
     * Memoize the request raw content in the $body property, if not already done.
     */
    private function initRawContent(): void
    {
        if ($this->body) {
            return;
        }
        $this->body = $this->request->rawContent();
    }
}
