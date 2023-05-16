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
namespace Hyperf\Session\Handler;

use Carbon\Carbon;
use Hyperf\Support\Filesystem\Filesystem;
use SessionHandlerInterface;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class FileHandler implements SessionHandlerInterface
{
    /**
     * @param string $path the path where sessions should be stored
     * @param int $minutes the number of minutes the session should be valid
     */
    public function __construct(private Filesystem $files, private string $path, protected int $minutes)
    {
        if (! file_exists($path)) {
            $files->makeDirectory($path, 0755, true);
        }
    }

    /**
     * Close the session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.close.php
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroy a session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $id the session ID being destroyed
     */
    public function destroy(string $id): bool
    {
        $this->files->delete($this->path . '/' . $id);
        return true;
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     */
    public function gc(int $max_lifetime): int|false
    {
        $files = Finder::create()
            ->in($this->path)
            ->files()
            ->ignoreDotFiles(true)
            ->date('<= now - ' . $max_lifetime . ' seconds');

        $count = 0;
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $this->files->delete($file->getRealPath());
            ++$count;
        }
        return $count;
    }

    /**
     * Initialize session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $path the path where to store/retrieve the session
     * @param string $name the session name
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Read session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $id the session id to read data for
     * @return string
     */
    public function read(string $id): string|false
    {
        if ($this->files->isFile($path = $this->path . '/' . $id)) {
            if ($this->files->lastModified($path) >= Carbon::now()->subMinutes($this->minutes)->getTimestamp()) {
                return $this->files->sharedGet($path);
            }
        }

        return '';
    }

    /**
     * Write session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $id the session id
     */
    public function write(string $id, string $data): bool
    {
        $this->files->put($this->path . '/' . $id, $data, true);

        $this->files->clearStatCache($this->path);

        return true;
    }
}
