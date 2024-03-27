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

namespace Hyperf\Codec;

use Hyperf\Codec\Exception\InvalidArgumentException;

class Base62
{
    public const CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public const BASE = 62;

    public static function encode(int $number): string
    {
        $chars = [];
        while ($number > 0) {
            $remain = $number % static::BASE;
            $chars[] = static::CHARS[$remain];
            $number = ($number - $remain) / static::BASE;
        }
        return implode('', array_reverse($chars));
    }

    public static function decode(string $data): int
    {
        if ($data === '' || strlen($data) !== strspn($data, self::CHARS)) {
            throw new InvalidArgumentException('The decode data contains content outside of CHARS.');
        }
        return array_reduce(array_map(function ($character) {
            return strpos(static::CHARS, $character);
        }, str_split($data)), function ($result, $remain) {
            return $result * static::BASE + $remain;
        });
    }
}
