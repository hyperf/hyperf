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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Channel;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\Watcher\Option;
use Symfony\Component\Finder\SplFileInfo;

class ScanFileDriver extends AbstractDriver
{
    protected Filesystem $filesystem;

    public function __construct(protected Option $option, private StdoutLoggerInterface $logger)
    {
        parent::__construct($option);
        $this->filesystem = new Filesystem();
    }

    public function watch(Channel $channel): void
    {
        $seconds = $this->option->getScanIntervalSeconds();
        $this->timerId = $this->timer->tick($seconds, function () use ($channel) {
            global $lastMD5;
            $files = [];
            $currentMD5 = $this->getWatchMD5($files);
            if ($lastMD5 && $lastMD5 !== $currentMD5) {
                $changeFilesMD5 = array_diff(array_values($lastMD5), array_values($currentMD5));
                $addFiles = array_diff(array_keys($currentMD5), array_keys($lastMD5));
                foreach ($addFiles as $file) {
                    $channel->push($file);
                }
                $deleteFiles = array_diff(array_keys($lastMD5), array_keys($currentMD5));
                $deleteCount = count($deleteFiles);

                $watchingLog = sprintf('%s Watching: Total:%d, Change:%d, Add:%d, Delete:%d.', self::class, count($currentMD5), count($changeFilesMD5), count($addFiles), $deleteCount);
                $this->logger->debug($watchingLog);

                if ($deleteCount == 0) {
                    $changeFilesIdx = array_keys($changeFilesMD5);
                    foreach ($changeFilesIdx as $idx) {
                        isset($files[$idx]) && $channel->push($files[$idx]);
                    }
                } else {
                    $this->logger->warning('Delete files must be restarted manually to take effect.');
                }
            }
            $lastMD5 = $currentMD5;
        });
    }

    protected function getWatchMD5(&$files): array
    {
        $filesMD5 = [];
        $filesObj = [];
        $dir = $this->option->getWatchDir();
        $ext = $this->option->getExt();
        // Scan all watch dirs.
        foreach ($dir as $d) {
            $filesObj = array_merge($filesObj, $this->filesystem->allFiles(BASE_PATH . '/' . $d));
        }
        /** @var SplFileInfo $obj */
        foreach ($filesObj as $obj) {
            $pathName = $obj->getPathName();
            if (Str::endsWith($pathName, $ext)) {
                $files[] = $pathName;
                $contents = file_get_contents($pathName);
                $filesMD5[$pathName] = md5($contents);
            }
        }
        // Scan all watch files.
        $file = $this->option->getWatchFile();
        $filesObj = $this->filesystem->files(BASE_PATH, true);
        /** @var SplFileInfo $obj */
        foreach ($filesObj as $obj) {
            $pathName = $obj->getPathName();
            if (Str::endsWith($pathName, $file)) {
                $files[] = $pathName;
                $contents = file_get_contents($pathName);
                $filesMD5[$pathName] = md5($contents);
            }
        }
        return $filesMD5;
    }
}
