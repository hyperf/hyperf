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

namespace Hyperf\Coroutine;

use Hyperf\Engine\Channel;

class Locker
{
    /**
     * @var Channel[]
     */
    protected static array $channels = [];

    public static function lock(string $key): bool
    {
        if (! isset(static::$channels[$key])) {
            static::$channels[$key] = new Channel(1);
            return true;
        }

        $channel = static::$channels[$key];
        $channel->pop(-1);
        return false;
    }

    public static function unlock(string $key): void
    {
        if (isset(static::$channels[$key])) {
            $channel = static::$channels[$key];
            static::$channels[$key] = null;
            $channel->close();
        }
    }
}
