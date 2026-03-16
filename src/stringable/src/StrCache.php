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

namespace Hyperf\Stringable;

class StrCache
{
    /**
     * The cache of snake-cased words.
     */
    protected static array $snakeCache = [];

    /**
     * The cache of camel-cased words.
     */
    protected static array $camelCache = [];

    /**
     * The cache of studly-cased words.
     */
    protected static array $studlyCache = [];

    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = Str::camel($value);
    }

    public static function snake(string $value, string $delimiter = '_'): string
    {
        if (isset(static::$snakeCache[$value][$delimiter])) {
            return static::$snakeCache[$value][$delimiter];
        }

        return static::$snakeCache[$value][$delimiter] = Str::snake($value, $delimiter);
    }

    public static function studly(string $value, string $gap = ''): string
    {
        if (isset(static::$studlyCache[$value][$gap])) {
            return static::$studlyCache[$value][$gap];
        }

        return static::$studlyCache[$value][$gap] = Str::studly($value, $gap);
    }
}
