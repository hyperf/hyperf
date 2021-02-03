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

use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Str;
use Hyperf\Watcher\Option;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\System;

class FswatchDriver implements DriverInterface
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
        $ret = System::exec('which fswatch');
        if (empty($ret['output'])) {
            throw new \InvalidArgumentException('fswatch not exists. You can `brew install fswatch` to install it.');
        }
    }

    public function watch(Channel $channel): void
    {
        $cmd = $this->getCmd();
        $process = proc_open($cmd, [['pipe', 'r'], ['pipe', 'w']], $pipes);
        if (! is_resource($process)) {
            throw new \RuntimeException('fswatch failed.');
        }

        while (true) {
            $ret = fread($pipes[1], 8192);
            Coroutine::create(function () use ($ret, $channel) {
                if (is_string($ret)) {
                    $files = array_filter(explode("\n", $ret));
                    foreach ($files as $file) {
                        if (Str::endsWith($file, $this->option->getExt())) {
                            $channel->push($file);
                        }
                    }
                }
            });
        }
    }

    protected function getCmd(): string
    {
        $dir = $this->option->getWatchDir();
        $file = $this->option->getWatchFile();

        $cmd = 'fswatch ';
        if (! $this->isDarwin) {
            $cmd .= ' -m inotify_monitor';
            $cmd .= " -E --format '%p' -r ";
            $cmd .= ' --event Created --event Updated --event Removed --event Renamed ';
        }

        return $cmd . implode(' ', $dir) . ' ' . implode(' ', $file);
    }
}
