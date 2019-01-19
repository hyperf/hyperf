<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp\CacheManager;

class Memory implements CacheInterface
{
    public static $data = [];

    public function set($key, $value)
    {
        static::$data[$key] = $value;
    }

    public function get($key, $default = null)
    {
        return static::$data[$key] ?? $default;
    }

    public function has($key): bool
    {
        return isset(static::$data[$key]);
    }
}
