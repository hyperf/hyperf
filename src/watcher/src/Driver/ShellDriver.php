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

class ShellDriver implements DriverInterface
{
    /**
     * @var Option
     */
    protected $option;

    public function __construct(Option $option)
    {
        $this->option = $option;
    }

    public function watch(Channel $channel): void
    {
        $ms = $this->option->getScanInterval();
        Timer::tick($ms, function () use ($channel, $ms) {
            global $updateFiles;
            if (is_null($updateFiles)) {
                $updateFiles = [];
            }

            $ret = $this->shellWatch($updateFiles, $ms);
            $updateFiles = $ret['update_files'];

            foreach ($ret['push_files'] as $file) {
                $channel->push($file);
            }
        });
    }

    protected function shellWatch($updateFiles, $ms): array
    {
        $pushFiles = [];
        $dirs = $this->option->getWatchDir();
        $files = $this->option->getWatchFile();
        $ext = $this->option->getExt();

        $dirs = array_map(function ($dir) {
            return BASE_PATH . '/' . $dir;
        }, $dirs);
        $seconds = ceil(($ms + 1000) / 1000);

        $minutes = sprintf('%.2f', $seconds / 60);
        // scan directory files
        $dest = implode(' ', $dirs);
        $ret = System::exec('find ' . $dest . ' -mmin ' . $minutes . ' -type f -printf "%p %T+' . PHP_EOL . '"');
        if ($ret['code'] === 0 && strlen($ret['output'])) {
            $stdout = $ret['output'];

            $lineArr = explode(PHP_EOL, $stdout);
            foreach ($lineArr as $line) {
                $fileArr = explode(' ', $line);
                if (count($fileArr) == 2) {
                    $pathName = $fileArr[0];
                    $modifyTime = $fileArr[1];

                    if (Str::endsWith($pathName, $ext)) {
                        if (isset($updateFiles[$pathName]) && $updateFiles[$pathName] == $modifyTime) {
                            continue;
                        }
                        $updateFiles[$pathName] = $modifyTime;
                        $pushFiles[] = $pathName;
                    }
                }
            }
        }
        // scan specify files
        $files = array_map(function ($file) {
            return BASE_PATH . '/' . $file;
        }, $files);
        $dest = implode(' ', $files);
        $ret = System::exec('find ' . $dest . ' -mmin ' . $minutes . ' -type f -printf "%p %T+' . PHP_EOL . '"');
        if ($ret['code'] === 0 && strlen($ret['output'])) {
            $stdout = $ret['output'];

            $lineArr = explode(PHP_EOL, $stdout);
            foreach ($lineArr as $line) {
                $fileArr = explode(' ', $line);
                if (count($fileArr) == 2) {
                    $pathName = $fileArr[0];
                    $modifyTime = $fileArr[1];

                    if (isset($updateFiles[$pathName]) && $updateFiles[$pathName] == $modifyTime) {
                        continue;
                    }
                    $updateFiles[$pathName] = $modifyTime;
                    $pushFiles[] = $pathName;
                }
            }
        }

        return [
            'update_files' => $updateFiles,
            'push_files' => $pushFiles,
        ];
    }
}
