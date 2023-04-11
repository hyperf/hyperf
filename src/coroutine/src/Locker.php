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

use Hyperf\Coroutine\Traits\Container;
use Hyperf\Engine\Constant;
use Hyperf\Engine\Coroutine as Co;
use Swoole\Coroutine as SwooleCoroutine;

class Locker
{
    use Container;

    public static function add(string $key, int $id): void
    {
        self::$container[$key][] = $id;
    }

    public static function clear(string $key): void
    {
        unset(self::$container[$key]);
    }

    public static function lock(string $key): bool
    {
        if (! self::has($key)) {
            self::add($key, 0);
            return true;
        }
        self::add($key, Coroutine::id());
        // TODO: When the verion of `hyperf/engine` >= 2.0, use `Co::yield()` instead.
        match (Constant::ENGINE) {
            'Swoole' => SwooleCoroutine::yield(),
            /* @phpstan-ignore-next-line */
            default => Co::yield(),
        };
        return false;
    }

    public static function unlock(string $key): void
    {
        if (self::has($key)) {
            $ids = self::get($key);
            foreach ($ids as $id) {
                if ($id > 0) {
                    // TODO: When the verion of `hyperf/engine` >= 2.0, use `Co::resumeById()` instead.
                    match (Constant::ENGINE) {
                        'Swoole' => SwooleCoroutine::resume($id),
                        /* @phpstan-ignore-next-line */
                        default => Co::resumeById($id),
                    };
                }
            }
            self::clear($key);
        }
    }
}
