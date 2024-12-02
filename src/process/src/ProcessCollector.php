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

use Swoole\Process;

/**
 * Only collect coroutine process.
 */
class ProcessCollector
{
    /**
     * @var array<string, array<Process>>
     */
    protected static array $processes = [];

    public static function add($name, Process $process)
    {
        static::$processes[$name][] = $process;
    }

    /**
     * @param string $name
     * @return Process[]
     */
    public static function get($name): array
    {
        return static::$processes[$name] ?? [];
    }

    /**
     * @return Process[]
     */
    public static function all(): array
    {
        $result = [];
        foreach (static::$processes as $processes) {
            $result = array_merge($result, $processes);
        }
        return $result;
    }

    public static function isEmpty(): bool
    {
        return static::$processes === [];
    }
}
