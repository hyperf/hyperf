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

namespace Hyperf\Logger\Handler;

use InvalidArgumentException;
use LogicException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Utils;
use Throwable;
use UnexpectedValueException;

use function dirname;
use function ini_get;
use function is_resource;
use function is_string;

/**
 * Stores to any stream resource.
 *
 * Can be used to store into php://stderr, remote and local files, etc.
 */
class StreamHandler extends AbstractProcessingHandler
{
    protected const MAX_CHUNK_SIZE = 2147483647;

    /** 10MB */
    protected const DEFAULT_CHUNK_SIZE = 10 * 1024 * 1024;

    protected int $streamChunkSize;

    /** @var null|resource */
    protected $stream;

    protected ?string $url = null;

    protected ?int $filePermission;

    protected bool $useLocking;

    protected string $fileOpenMode;

    /** @var null|true */
    private ?bool $dirCreated = null;

    private bool $retrying = false;

    private ?int $inodeUrl = null;

    /**
     * @param resource|string $stream If a missing path can't be created, an UnexpectedValueException will be thrown on first write
     * @param null|int $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param bool $useLocking Try to lock log file before doing any writes
     * @param string $fileOpenMode The fopen() mode used when opening a file, if $stream is a file path
     *
     * @throws InvalidArgumentException If stream is not a resource or string
     */
    public function __construct($stream, int|Level|string $level = Level::Debug, bool $bubble = true, ?int $filePermission = null, bool $useLocking = false, string $fileOpenMode = 'a')
    {
        parent::__construct($level, $bubble);

        if (($phpMemoryLimit = Utils::expandIniShorthandBytes(ini_get('memory_limit'))) !== false) {
            if ($phpMemoryLimit > 0) {
                // use max 10% of allowed memory for the chunk size, and at least 100KB
                $this->streamChunkSize = min(static::MAX_CHUNK_SIZE, max((int) ($phpMemoryLimit / 10), 100 * 1024));
            } else {
                // memory is unlimited, set to the default 10MB
                $this->streamChunkSize = static::DEFAULT_CHUNK_SIZE;
            }
        } else {
            // no memory limit information, set to the default 10MB
            $this->streamChunkSize = static::DEFAULT_CHUNK_SIZE;
        }

        if (is_resource($stream)) {
            $this->stream = $stream;

            stream_set_chunk_size($this->stream, $this->streamChunkSize);
        } elseif (is_string($stream)) {
            $this->url = Utils::canonicalizePath($stream);
        } else {
            throw new InvalidArgumentException('A stream must either be a resource or a string.');
        }

        $this->fileOpenMode = $fileOpenMode;
        $this->filePermission = $filePermission;
        $this->useLocking = $useLocking;
    }

    public function reset(): void
    {
        parent::reset();

        // auto-close on reset to make sure we periodically close the file in long running processes
        // as long as they correctly call reset() between jobs
        if ($this->url !== null && $this->url !== 'php://memory') {
            $this->close();
        }
    }

    public function close(): void
    {
        if ($this->url !== null && is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
        $this->dirCreated = null;
    }

    /**
     * Return the currently active stream if it is open.
     *
     * @return null|resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Return the stream URL if it was configured with a URL and not an active resource.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getStreamChunkSize(): int
    {
        return $this->streamChunkSize;
    }

    protected function write(LogRecord $record): void
    {
        if ($this->hasUrlInodeWasChanged()) {
            $this->close();
            $this->write($record);

            return;
        }

        if (! is_resource($this->stream)) {
            $url = $this->url;
            if ($url === null || $url === '') {
                throw new LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().' . Utils::getRecordMessageForException($record));
            }
            $this->createDir($url);

            $stream = fopen($url, $this->fileOpenMode);
            if ($this->filePermission !== null) {
                chmod($url, $this->filePermission);
            }

            if (! is_resource($stream)) {
                $this->stream = null;

                throw new UnexpectedValueException(sprintf('The stream or file "%s" could not be opened in append mode', $url) . Utils::getRecordMessageForException($record));
            }

            stream_set_chunk_size($stream, $this->streamChunkSize);
            $this->stream = $stream;
            $this->inodeUrl = $this->getInodeFromUrl();
        }

        $stream = $this->stream;
        if ($this->useLocking) {
            // ignoring errors here, there's not much we can do about them
            flock($stream, LOCK_EX);
        }

        try {
            $this->streamWrite($stream, $record);
        } catch (Throwable $exception) {
            // close the resource if possible to reopen it, and retry the failed write
            if (! $this->retrying && $this->url !== null && $this->url !== 'php://memory') {
                $this->retrying = true;
                $this->close();
                $this->write($record);

                return;
            }

            throw new UnexpectedValueException('Writing to the log file failed: ' . ((string) $exception) . Utils::getRecordMessageForException($record));
        }

        $this->retrying = false;
        if ($this->useLocking) {
            flock($stream, LOCK_UN);
        }
    }

    /**
     * Write to stream.
     * @param resource $stream
     */
    protected function streamWrite($stream, LogRecord $record): void
    {
        fwrite($stream, (string) $record->formatted);
    }

    private function getDirFromStream(string $stream): ?string
    {
        $pos = strpos($stream, '://');
        if ($pos === false) {
            return dirname($stream);
        }

        if (substr($stream, 0, 7) === 'file://') {
            return dirname(substr($stream, 7));
        }

        return null;
    }

    private function createDir(string $url): void
    {
        // Do not try to create dir if it has already been tried.
        if ($this->dirCreated === true) {
            return;
        }

        $dir = $this->getDirFromStream($url);
        if ($dir !== null && ! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->dirCreated = true;
    }

    private function getInodeFromUrl(): ?int
    {
        if ($this->url === null || str_starts_with($this->url, 'php://')) {
            return null;
        }

        $inode = @fileinode($this->url);

        return $inode === false ? null : $inode;
    }

    private function hasUrlInodeWasChanged(): bool
    {
        if ($this->inodeUrl === null || $this->retrying || $this->inodeUrl === $this->getInodeFromUrl()) {
            return false;
        }

        $this->retrying = true;

        return true;
    }
}
