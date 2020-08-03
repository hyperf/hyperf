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
    protected $isDarwin;

    public function __construct(Option $option)
    {
        $this->option = $option;
        $this->isDarwin = PHP_OS === 'Darwin';
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
        }
    }

    public function watch(Channel $channel): void
    {
        $ms = $this->option->getScanInterval();
        Timer::tick($ms, function () use ($channel, $ms) {
            global $fileModifyTimes;
            if (is_null($fileModifyTimes)) {
                $fileModifyTimes = [];
            }

            $seconds = ceil(($ms + 1000) / 1000);
            $minutes = sprintf('-%.2f', $seconds / 60);

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
        $ret = System::exec($this->getBin() . ' ' . $dest . ' -mmin ' . $minutes . ' -type f -printf "%p %T+' . PHP_EOL . '"');
        if ($ret['code'] === 0 && strlen($ret['output'])) {
            $stdout = $ret['output'];

            $lineArr = explode(PHP_EOL, $stdout);
            foreach ($lineArr as $line) {
                $fileArr = explode(' ', $line);
                if (count($fileArr) == 2) {
                    $pathName = $fileArr[0];
                    $modifyTime = $fileArr[1];

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
