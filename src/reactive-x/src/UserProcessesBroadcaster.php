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

namespace Hyperf\ReactiveX;

use Hyperf\Process\ProcessCollector;
use Hyperf\ReactiveX\Contract\BroadcasterInterface;

class UserProcessesBroadcaster implements BroadcasterInterface
{
    public function __construct(protected ?string $name = null, protected ?int $id = null)
    {
    }

    public function broadcast(IpcMessageWrapper $message): void
    {
        if ($this->id !== null) {
            $processes = ProcessCollector::get($this->name);
            $processes[$this->id]->write(serialize($message));
            return;
        }

        if ($this->name !== null) {
            $processes = ProcessCollector::get($this->name);
        } else {
            $processes = ProcessCollector::all();
        }

        foreach ($processes as $process) {
            $process->write(serialize($message));
        }
    }
}
