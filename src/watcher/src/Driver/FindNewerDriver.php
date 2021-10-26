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

class FindNewerDriver implements DriverInterface
{
    /**
     * @var Option
     */
    protected $option;

    /**
     * @var string
     */
    protected $tmpFile = '/tmp/hyperf_find.php';

    /**
     * @var bool
     */
    protected $scaning = false;

    /**
     * @var int
     */
    protected $count = 0;

    public function __construct(Option $option)
    {
        $this->option = $option;

        $ret = System::exec('which find');
        if (empty($ret['output'])) {
            throw new \InvalidArgumentException('find not exists.');
        }
        // create two files
        System::exec('echo 1 > ' . $this->getToModifyFile());
        System::exec('echo 1 > ' . $this->getToScanFile());
    }

    public function watch(Channel $channel): void
    {
        $ms = $this->option->getScanInterval();
        Timer::tick($ms, function () use ($channel) {
            if ($this->scaning == false) {
                $this->scaning = true;

                System::exec('echo 1 > ' . $this->getToModifyFile());
                $changedFiles = $this->scan();

                $this->scaning = false;
                ++$this->count;
                foreach ($changedFiles as $file) {
                    $channel->push($file);
                    return;
                }
            }
        });
    }

    protected function find(array $targets, array $ext = []): array
    {
        $changedFiles = [];

        $shell = '';
        $len = count($targets);
        // merge find command
        for ($i = 0; $i < $len; ++$i) {
            $dest = $targets[$i];
            $symbol = ($i == $len - 1) ? '' : '&';
            $file = $this->getToScanFile();
            $shell = $shell . sprintf('find %s -newer %s -type f', $dest, $file) . $symbol;
        }

        $ret = System::exec($shell);
        if ($ret['code'] === 0 && strlen($ret['output'])) {
            $stdout = $ret['output'];
            $lineArr = explode(PHP_EOL, $stdout);
            foreach ($lineArr as $pathName) {
                if (empty($pathName)) {
                    continue;
                }

                if (! empty($ext) && ! Str::endsWith($pathName, $ext)) {
                    continue;
                }
                $changedFiles[] = $pathName;
            }
        }

        return $changedFiles;
    }

    protected function scan(): array
    {
        $ext = $this->option->getExt();

        $dirs = array_map(function ($dir) {
            return BASE_PATH . '/' . $dir;
        }, $this->option->getWatchDir());

        $files = array_map(function ($file) {
            return BASE_PATH . '/' . $file;
        }, $this->option->getWatchFile());

        if ($files) {
            $dirs[] = implode(' ', $files);
        }

        return $this->find($dirs, $ext);
    }

    protected function getToModifyFile()
    {
        return $this->tmpFile . strval($this->count % 2);
    }

    protected function getToScanFile()
    {
        return $this->tmpFile . strval(($this->count + 1) % 2);
    }
}
