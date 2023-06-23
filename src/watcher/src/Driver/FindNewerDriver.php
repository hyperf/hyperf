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

class FindNewerDriver extends AbstractDriver
{
    protected string $tmpFile = '/tmp/hyperf_find.php';

    protected bool $scanning = false;

    protected int $count = 0;

    public function __construct(protected Option $option)
    {
        parent::__construct($option);
        $ret = exec('which find');
        if (empty($ret['output'])) {
            throw new InvalidArgumentException('find not exists.');
        }
        // create two files
        exec('echo 1 > ' . $this->getToModifyFile());
        exec('echo 1 > ' . $this->getToScanFile());
    }

    public function watch(Channel $channel): void
    {
        $seconds = $this->option->getScanIntervalSeconds();
        $this->timerId = $this->timer->tick($seconds, function () use ($channel) {
            if ($this->scanning) {
                return;
            }
            $this->scanning = true;
            $changedFiles = $this->scan();
            ++$this->count;
            // update mtime
            if ($changedFiles) {
                exec('echo 1 > ' . $this->getToModifyFile());
                exec('echo 1 > ' . $this->getToScanFile());
            }

            foreach ($changedFiles as $file) {
                $channel->push($file);
                $this->scanning = false;
                return;
            }
            $this->scanning = false;
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

        $ret = exec($shell);
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
        $dirs = array_map(fn ($dir) => BASE_PATH . '/' . $dir, $this->option->getWatchDir());
        $files = array_map(fn ($file) => BASE_PATH . '/' . $file, $this->option->getWatchFile());

        if ($files) {
            $dirs[] = implode(' ', $files);
        }

        return $this->find($dirs, $ext);
    }

    protected function getToModifyFile(): string
    {
        return $this->tmpFile . ($this->count % 2);
    }

    protected function getToScanFile(): string
    {
        return $this->tmpFile . (($this->count + 1) % 2);
    }
}
