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
namespace Hyperf\Utils;

/**
 * @deprecated v3.1, use \Hyperf\Support\ClearStatCache instead.
 */
class ClearStatCache
{
    /**
     * Interval at which to clear filesystem stat cache. Values below 1 indicate
     * the stat cache should ALWAYS be cleared. Otherwise, the value is the number
     * of seconds between clear operations.
     */
    private static int $interval = 1;

    /**
     * When the filesystem stat cache was last cleared.
     */
    private static int $lastCleared = 0;

    public static function clear(?string $filename = null): void
    {
        $now = time();
        if (1 > self::$interval
            || self::$lastCleared
            || (self::$lastCleared + self::$interval < $now)
        ) {
            self::forceClear($filename);
            self::$lastCleared = $now;
        }
    }

    public static function forceClear(?string $filename = null): void
    {
        if ($filename !== null) {
            clearstatcache(true, $filename);
        } else {
            clearstatcache();
        }
    }

    public static function getInterval(): int
    {
        return self::$interval;
    }

    public static function setInterval(int $interval)
    {
        self::$interval = $interval;
    }
}
