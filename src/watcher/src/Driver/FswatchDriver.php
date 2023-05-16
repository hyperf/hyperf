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
use Hyperf\Engine\Coroutine;
use Hyperf\Stringable\Str;
use Hyperf\Watcher\Option;
use InvalidArgumentException;
use RuntimeException;

use function Hyperf\Watcher\exec;

class FswatchDriver extends AbstractDriver
{
    protected mixed $process = null;

    public function __construct(protected Option $option)
    {
        parent::__construct($option);
        $ret = exec('which fswatch');
        if (empty($ret['output'])) {
            throw new InvalidArgumentException('fswatch not exists. You can `brew install fswatch` to install it.');
        }
    }

    public function watch(Channel $channel): void
    {
        $cmd = $this->getCmd();
        $this->process = proc_open($cmd, [['pipe', 'r'], ['pipe', 'w']], $pipes);
        if (! is_resource($this->process)) {
            throw new RuntimeException('fswatch failed.');
        }

        while (! $channel->isClosing()) {
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

    public function stop()
    {
        parent::stop();

        if (is_resource($this->process)) {
            $running = proc_get_status($this->process)['running'];
            // Kill the child process to exit.
            $running && proc_terminate($this->process, SIGKILL);
        }
    }

    protected function getCmd(): string
    {
        $dir = $this->option->getWatchDir();
        $file = $this->option->getWatchFile();

        $cmd = 'fswatch ';
        if (! $this->isDarwin()) {
            $cmd .= ' -m inotify_monitor';
            $cmd .= " -E --format '%p' -r ";
            $cmd .= ' --event Created --event Updated --event Removed --event Renamed ';
        }

        return $cmd . implode(' ', $dir) . ' ' . implode(' ', $file);
    }
}
