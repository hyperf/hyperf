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

namespace Hyperf\Process;

use Hyperf\Contract\ProcessInterface;
use RuntimeException;

class ProcessManager
{
    protected static array $processes = [];

    protected static bool $running = false;

    public static function register(ProcessInterface $process): void
    {
        if (static::$running) {
            throw new RuntimeException('Processes is running, please register before BeforeMainServerStart or MainCoroutineServerStart dispatched.');
        }

        static::$processes[] = $process;
    }

    public static function all(): array
    {
        return static::$processes;
    }

    public static function clear(): void
    {
        static::$processes = [];
    }

    public static function isRunning(): bool
    {
        return static::$running;
    }

    public static function setRunning(bool $running): void
    {
        static::$running = $running;
    }
}
