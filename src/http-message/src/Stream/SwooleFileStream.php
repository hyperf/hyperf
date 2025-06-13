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

namespace Hyperf\HttpMessage\Stream;

use BadMethodCallException;
use Hyperf\HttpServer\Exception\Http\FileException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use SplFileInfo;
use Stringable;
use Throwable;

class SwooleFileStream implements StreamInterface, FileInterface, Stringable
{
    protected int $size;

    protected SplFileInfo $file;

    /**
     * SwooleFileStream constructor.
     *
     * @param SplFileInfo|string $file
     */
    public function __construct($file)
    {
        if (! $file instanceof SplFileInfo) {
            $file = new SplFileInfo($file);
        }
        if (! $file->isReadable()) {
            throw new FileException('File must be readable.');
        }
        $this->file = $file;
        $this->size = $file->getSize();
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     * Warning: This could attempt to load a large amount of data into memory.
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     */
    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close(): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Separates any underlying resources from the stream.
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return null|resource Underlying PHP stream, if any
     */
    public function detach()
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Get the size of the stream if known.
     *
     * @return null|int returns the size in bytes if known, or null if unknown
     */
    public function getSize(): ?int
    {
        if (! $this->size) {
            $this->size = filesize($this->getContents());
        }
        return $this->size;
    }

    /**
     * Returns the current position of the file read/write pointer.
     *
     * @return int Position of the file pointer
     * @throws RuntimeException on error
     */
    public function tell(): int
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Returns true if the stream is at the end of the stream.
     */
    public function eof(): bool
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Returns whether the stream is seekable.
     */
    public function isSeekable(): bool
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Seek to a position in the stream.
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     * @throws RuntimeException on failure
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Seek to the beginning of the stream.
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @throws RuntimeException on failure
     * @see http://www.php.net/manual/en/function.fseek.php
     * @see seek()
     */
    public function rewind(): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Returns whether the stream is writable.
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string the string that is to be written
     * @return int returns the number of bytes written to the stream
     * @throws RuntimeException on failure
     */
    public function write($string): int
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Returns whether the stream is readable.
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return them.
     *                    Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     * @return string returns the data read from the stream, or an empty string
     *                if no bytes are available
     * @throws RuntimeException if an error occurs
     */
    public function read($length): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @throws RuntimeException if unable to read or an error occurs while
     *                          reading
     */
    public function getContents(): string
    {
        return $this->getFilename();
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key specific metadata to retrieve
     * @return null|array|mixed Returns an associative array if no key is
     *                          provided. Returns a specific key value if a key is provided and the
     *                          value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getFilename(): string
    {
        return $this->file->getPathname();
    }
}
