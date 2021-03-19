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
namespace Hyperf\Watcher\Driver;

use Hyperf\Utils\Str;
use Hyperf\Watcher\Option;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\System;
use Swoole\Timer;

class FindDriver implements DriverInterface
{
    /**
     * @var Option
     */
    protected $option;

    /**
     * @var bool
     */
    protected $isDarwin = false;

    /**
     * @var bool
     */
    protected $isSupportFloatMinutes = true;

    /**
     * @var int
     */
    protected $startTime;

    public function __construct(Option $option)
    {
        $this->option = $option;
        if (PHP_OS === 'Darwin') {
            $this->isDarwin = true;
        } else {
            $this->isDarwin = false;
        }
        if ($this->isDarwin) {
            $ret = System::exec('which gfind');
            if (empty($ret['output'])) {
                throw new \InvalidArgumentException('gfind not exists. You can `brew install findutils` to install it.');
            }
        } else {
            $ret = System::exec('which find');
            if (empty($ret['output'])) {
                throw new \InvalidArgumentException('find not exists.');
            }
            $ret = System::exec('find --help', true);
            $this->isSupportFloatMinutes = (strpos($ret['output'] ?? '', 'BusyBox')) === false;
        }
    }

    public function watch(Channel $channel): void
    {
        $this->startTime = time();
        $ms = $this->option->getScanInterval();
        $seconds = ceil(($ms + 1000) / 1000);
        if ($this->isSupportFloatMinutes) {
            $minutes = sprintf('-%.2f', $seconds / 60);
        } else {
            $minutes = sprintf('-%d', ceil($seconds / 60));
        }
        Timer::tick($ms, function () use ($channel, $minutes) {
            global $fileModifyTimes;
            if (is_null($fileModifyTimes)) {
                $fileModifyTimes = [];
            }

            [$fileModifyTimes, $changedFiles] = $this->scan($fileModifyTimes, $minutes);

            foreach ($changedFiles as $file) {
                $channel->push($file);
            }
        });
    }

    protected function find(array $fileModifyTimes, array $targets, string $minutes, array $ext = []): array
    {
        $changedFiles = [];
        $dest = implode(' ', $targets);
        $ret = System::exec($this->getBin() . ' ' . $dest . ' -mmin ' . $minutes . ' -type f -print');
        if ($ret['code'] === 0 && strlen($ret['output'])) {
            $stdout = trim($ret['output']);

            $lineArr = explode(PHP_EOL, $stdout);
            foreach ($lineArr as $line) {
                $pathName = $line;
                $modifyTime = filemtime($pathName);
                // modifyTime less than or equal to startTime continue
                if ($modifyTime <= $this->startTime) {
                    continue;
                }
                if (! empty($ext) && ! Str::endsWith($pathName, $ext)) {
                    continue;
                }

                if (isset($fileModifyTimes[$pathName]) && $fileModifyTimes[$pathName] == $modifyTime) {
                    continue;
                }
                $fileModifyTimes[$pathName] = $modifyTime;
                $changedFiles[] = $pathName;
            }
        }

        return [$fileModifyTimes, $changedFiles];
    }

    protected function getBin(): string
    {
        return $this->isDarwin ? 'gfind' : 'find';
    }

    protected function scan(array $fileModifyTimes, string $minutes): array
    {
        $ext = $this->option->getExt();

        $dirs = array_map(function ($dir) {
            return BASE_PATH . '/' . $dir;
        }, $this->option->getWatchDir());

        [$fileModifyTimes, $changedFilesInDirs] = $this->find($fileModifyTimes, $dirs, $minutes, $ext);

        $files = array_map(function ($file) {
            return BASE_PATH . '/' . $file;
        }, $this->option->getWatchFile());

        [$fileModifyTimes, $changedFiles] = $this->find($fileModifyTimes, $files, $minutes);

        return [$fileModifyTimes, array_merge($changedFilesInDirs, $changedFiles)];
    }
}
