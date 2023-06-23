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

use Hyperf\Engine\Channel;
use Hyperf\Stringable\Str;
use Hyperf\Watcher\Option;
use InvalidArgumentException;

use function Hyperf\Watcher\exec;

class FindDriver extends AbstractDriver
{
    protected bool $isSupportFloatMinutes = true;

    protected int $startTime = 0;

    public function __construct(protected Option $option)
    {
        parent::__construct($option);

        if ($this->isDarwin()) {
            $ret = exec('which gfind');
            if (empty($ret['output'])) {
                throw new InvalidArgumentException('gfind not exists. You can `brew install findutils` to install it.');
            }
        } else {
            $ret = exec('which find');
            if (empty($ret['output'])) {
                throw new InvalidArgumentException('find not exists.');
            }
            $ret = exec('find --help');
            $this->isSupportFloatMinutes = ! str_contains($ret['output'] ?? '', 'BusyBox');
        }
    }

    public function watch(Channel $channel): void
    {
        $this->startTime = time();
        $seconds = $this->option->getScanIntervalSeconds();

        $this->timerId = $this->timer->tick($seconds, function () use ($channel) {
            global $fileModifyTimes;
            if (is_null($fileModifyTimes)) {
                $fileModifyTimes = [];
            }

            [$fileModifyTimes, $changedFiles] = $this->scan($fileModifyTimes, $this->getScanIntervalMinutes());

            foreach ($changedFiles as $file) {
                $channel->push($file);
            }
        });
    }

    protected function getScanIntervalMinutes(): string
    {
        $minutes = $this->option->getScanIntervalSeconds() / 60;
        if ($this->isSupportFloatMinutes) {
            return sprintf('-%.2f', $minutes);
        }
        return sprintf('-%d', ceil($minutes));
    }

    protected function find(array $fileModifyTimes, array $targets, string $minutes, array $ext = []): array
    {
        $changedFiles = [];
        $dest = implode(' ', $targets);
        $ret = exec($this->getBin() . ' ' . $dest . ' -mmin ' . $minutes . ' -type f -print');
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
        return $this->isDarwin() ? 'gfind' : 'find';
    }

    protected function scan(array $fileModifyTimes, string $minutes): array
    {
        $ext = $this->option->getExt();

        $dirs = array_map(fn ($dir) => BASE_PATH . '/' . $dir, $this->option->getWatchDir());

        [$fileModifyTimes, $changedFilesInDirs] = $this->find($fileModifyTimes, $dirs, $minutes, $ext);

        $files = array_map(fn ($file) => BASE_PATH . '/' . $file, $this->option->getWatchFile());

        [$fileModifyTimes, $changedFiles] = $this->find($fileModifyTimes, $files, $minutes);

        return [$fileModifyTimes, array_merge($changedFilesInDirs, $changedFiles)];
    }
}
