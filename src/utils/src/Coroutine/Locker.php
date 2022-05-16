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
namespace Hyperf\Utils\Coroutine;

use Hyperf\Engine\Constant;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Traits\Container;
use Swoole\Coroutine as SwooleCoroutine;

class Locker
{
    use Container;

    public static function add($key, $id): void
    {
        self::$container[$key][] = $id;
    }

    public static function clear($key): void
    {
        unset(self::$container[$key]);
    }

    public static function lock($key): bool
    {
        if (! self::has($key)) {
            self::add($key, 0);
            return true;
        }
        self::add($key, Coroutine::id());
        // When the verion of `hyperf/engine` >= 2.0, use `Co::yield()` instead.
        match (Constant::ENGINE) {
            'Swoole' => SwooleCoroutine::yield(),
            default => Co::yield(),
        };
        return false;
    }

    public static function unlock($key): void
    {
        if (self::has($key)) {
            $ids = self::get($key);
            foreach ($ids as $id) {
                if ($id > 0) {
                    // When the verion of `hyperf/engine` >= 2.0, use `Co::resumeById()` instead.
                    match (Constant::ENGINE) {
                        'Swoole' => SwooleCoroutine::resume($id),
                        default => Co::resumeById($id),
                    };
                }
            }
            self::clear($key);
        }
    }
}
