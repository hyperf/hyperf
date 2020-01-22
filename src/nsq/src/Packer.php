<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Nsq;

class Packer
{
    public static function packUInt32($int)
    {
        return pack('N', $int);
    }

    public static function unpackInt64($int)
    {
        return unpack('q', $int)[1];
    }

    public static function unpackUInt16($int)
    {
        return unpack('v', $int)[1];
    }

    public static function unpackString(string $content): string
    {
        $size = strlen($content);
        $bytes = unpack("c{$size}chars", $content);
        $string = '';
        foreach ($bytes as $byte) {
            $string .= chr($byte);
        }
        return $string;
    }
}
