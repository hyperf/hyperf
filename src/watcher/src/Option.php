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
namespace Hyperf\Watcher;

use Hyperf\Contract\ConfigInterface;

class Option
{
    /**
     * @var string
     */
    protected $driver = 'fswatch';

    /**
     * @var string
     */
    protected $bin = 'php';

    /**
     * @var string[]
     */
    protected $watchDir = ['app', 'config'];

    /**
     * @var string[]
     */
    protected $watchFile = ['.env'];

    /**
     * @var string[]
     */
    protected $ext = ['.php', '.env'];

    public function __construct(ConfigInterface $config, array $dir, array $file)
    {
        $options = $config->get('watcher', []);

        isset($options['driver']) && $this->driver = $options['driver'];
        isset($options['bin']) && $this->bin = $options['bin'];
        isset($options['watch']['dir']) && $this->watchDir = (array) $options['watch']['dir'];
        isset($options['watch']['file']) && $this->watchFile = (array) $options['watch']['file'];
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
}
