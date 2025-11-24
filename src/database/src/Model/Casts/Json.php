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

use JsonException;

class Json
{
    /**
     * The custom JSON encoder.
     *
     * @var null|callable
     */
    protected static $encoder;

    /**
     * The custom JSON decoder.
     *
     * @var null|callable
     */
    protected static $decoder;

    /**
     * Encode the given value.
     */
    public static function encode(mixed $value, int $flags = 0): false|string
    {
        return isset(static::$encoder)
            ? (static::$encoder)($value, $flags)
            : json_encode($value, $flags);
    }

    /**
     * Decode the given value.
     *
     * @param mixed $value The JSON string to decode
     * @param null|bool $associative When true, JSON objects will be returned as associative arrays; when false, as objects
     * @return mixed The decoded value, or null if the JSON string is invalid or represents null
     * @throws JsonException When JSON decoding fails (if custom decoder is not set and JSON_THROW_ON_ERROR is used)
     *
     * @note This method returns null both when the JSON string is "null" (valid JSON null)
     *       and when the JSON string is invalid/malformed (decode failure).
     *       Use json_last_error() after calling this method to distinguish between these cases.
     */
    public static function decode(mixed $value, ?bool $associative = true): mixed
    {
        if (isset(static::$decoder)) {
            return (static::$decoder)($value, $associative);
        }

        $decoded = json_decode($value, $associative);

        // Check for JSON decode errors
        // Note: json_decode() returns null both for valid JSON null and decode failures.
        // Use json_last_error() immediately after this call to distinguish between them.
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Return null on decode failure, but error can be checked via json_last_error()
            return null;
        }

        return $decoded;
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
