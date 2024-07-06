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

namespace Hyperf\Watcher;

use Hyperf\Watcher\Driver\ScanFileDriver;

class Option
{
    protected string $driver = ScanFileDriver::class;

    protected string $bin = PHP_BINARY;

    protected string $command = 'vendor/hyperf/watcher/watcher.php start';

    /**
     * @var string[]
     */
    protected array $watchDir = ['app', 'config'];

    /**
     * @var string[]
     */
    protected array $watchFile = ['.env'];

    /**
     * @var string[]
     */
    protected array $ext = ['.php', '.env'];

    protected int $scanInterval = 2000;

    public function __construct(array $options = [], array $dir = [], array $file = [], protected bool $restart = true)
    {
        isset($options['driver']) && $this->driver = $options['driver'];
        isset($options['bin']) && $this->bin = $options['bin'];
        isset($options['command']) && $this->command = $options['command'];
        isset($options['watch']['dir']) && $this->watchDir = (array) $options['watch']['dir'];
        isset($options['watch']['file']) && $this->watchFile = (array) $options['watch']['file'];
        isset($options['watch']['scan_interval']) && $this->scanInterval = (int) $options['watch']['scan_interval'];
        isset($options['ext']) && $this->ext = (array) $options['ext'];

        $this->watchDir = array_unique(array_merge($this->watchDir, $dir));
        $this->watchFile = array_unique(array_merge($this->watchFile, $file));
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getBin(): string
    {
        return $this->bin;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getWatchDir(): array
    {
        return $this->watchDir;
    }

    public function getWatchFile(): array
    {
        return $this->watchFile;
    }

    public function getExt(): array
    {
        return $this->ext;
    }

    public function getScanInterval(): int
    {
        return $this->scanInterval > 0 ? $this->scanInterval : 2000;
    }

    public function getScanIntervalSeconds(): float
    {
        return $this->getScanInterval() / 1000;
    }

    public function isRestart(): bool
    {
        return $this->restart;
    }
}
