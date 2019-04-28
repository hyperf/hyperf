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

namespace Hyperf\Utils\Traits;

trait Container
{
    /**
     * @var array
     */
    protected static $container = [];

    /**
     * {@inheritdoc}
     */
    public static function set(string $id, $value)
    {
        static::$container[$id] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function get(string $id, $default = null)
    {
        return static::$container[$id] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public static function has(string $id)
    {
        return isset(static::$container[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public static function list(): array
    {
        return static::$container;
    }
}
