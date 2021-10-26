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
namespace Hyperf\AsyncQueue\Signal;

use Hyperf\Process\ProcessManager;
use Hyperf\Signal\SignalHandlerInterface;

/**
 * @deprecated v2.2 use Hyperf\Process\Handler\ProcessStopHandler instead.
 */
class DriverStopHandler implements SignalHandlerInterface
{
    public function listen(): array
    {
        return [
            [self::PROCESS, SIGTERM],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);
    }
}
