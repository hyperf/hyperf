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
use Hyperf\Utils\Filesystem\Filesystem;
use SessionHandlerInterface;
use Symfony\Component\Finder\Finder;

class FileHandler implements SessionHandlerInterface
{
    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $minutes;

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    private $path;

    public function __construct(Filesystem $files, string $path, int $minutes)
    {
        $this->files = $files;
        $this->path = $path;
        $this->minutes = $minutes;
        if (! file_exists($path)) {
            $files->makeDirectory($path, 0755, true);
        }
    }

    /**
     * Close the session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy a session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id the session ID being destroyed
     * @return bool
     */
    public function destroy($session_id)
    {
        $this->files->delete($this->path . '/' . $session_id);
        return true;
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        $files = Finder::create()
            ->in($this->path)
            ->files()
            ->ignoreDotFiles(true)
            ->date('<= now - ' . $maxlifetime . ' seconds');

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $this->files->delete($file->getRealPath());
        }
        return true;
    }

    /**
     * Initialize session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path the path where to store/retrieve the session
     * @param string $name the session name
     * @return bool
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * Read session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id the session id to read data for
     * @return string
     */
    public function read($session_id)
    {
        if ($this->files->isFile($path = $this->path . '/' . $session_id)) {
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
     * @param string $session_id the session id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $this->files->put($this->path . '/' . $session_id, $session_data, true);

        return true;
    }
}
