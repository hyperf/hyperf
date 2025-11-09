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

namespace Hyperf\Database\Model\Casts;

class Json
{
    /**
     * The custom JSON encoder.
     *
     * @var callable|null
     */
    protected static $encoder;

    /**
     * The custom JSON decoder.
     *
     * @var callable|null
     */
    protected static $decoder;

    /**
     * Encode the given value.
     */
    public static function encode(mixed $value, int $flags = 0): mixed
    {
        return isset(static::$encoder)
            ? (static::$encoder)($value, $flags)
            : json_encode($value, $flags);
    }

    /**
     * Decode the given value.
     */
    public static function decode(mixed $value, ?bool $associative = true): mixed
    {
        return isset(static::$decoder)
            ? (static::$decoder)($value, $associative)
            : json_decode($value, $associative);
    }

    /**
     * Encode all values using the given callable.
     */
    public static function encodeUsing(?callable $encoder): void
    {
        static::$encoder = $encoder;
    }

    /**
     * Decode all values using the given callable.
     */
    public static function decodeUsing(?callable $decoder): void
    {
        static::$decoder = $decoder;
    }
}
