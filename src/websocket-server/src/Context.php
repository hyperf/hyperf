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

namespace Hyperf\WebSocketServer;

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Context\Context as CoContext;

use function Hyperf\Collection\data_get;
use function Hyperf\Collection\data_set;
use function Hyperf\Support\value;

class Context
{
    public const FD = 'ws.fd';

    protected static array $container = [];

    public static function set(string $id, $value)
    {
        $fd = CoContext::get(Context::FD, 0);
        $key = sprintf('%d.%s', $fd, $id);
        data_set(self::$container, $key, $value);
        return $value;
    }

    public static function get(string $id, $default = null, $fd = null)
    {
        $fd ??= CoContext::get(Context::FD, 0);
        $key = sprintf('%d.%s', $fd, $id);
        return data_get(self::$container, $key, $default);
    }

    public static function has(string $id, $fd = null)
    {
        $fd ??= CoContext::get(Context::FD, 0);
        $key = sprintf('%d.%s', $fd, $id);
        return data_get(self::$container, $key) !== null;
    }

    public static function destroy(string $id)
    {
        $fd = CoContext::get(Context::FD, 0);
        unset(self::$container[strval($fd)][$id]);
    }

    public static function release(?int $fd = null)
    {
        $fd ??= CoContext::get(Context::FD, 0);
        unset(self::$container[strval($fd)]);
    }

    public static function copy(int $fromFd, array $keys = []): void
    {
        $fd = CoContext::get(Context::FD, 0);
        $from = self::$container[$fromFd];
        self::$container[$fd] = ($keys ? Arr::only($from, $keys) : $from);
    }

    public static function override(string $id, Closure $closure)
    {
        $value = null;
        if (self::has($id)) {
            $value = self::get($id);
        }
        $value = $closure($value);
        self::set($id, $value);
        return $value;
    }

    public static function getOrSet(string $id, $value)
    {
        if (! self::has($id)) {
            return self::set($id, value($value));
        }
        return self::get($id);
    }

    public static function getContainer()
    {
        return self::$container;
    }
}
