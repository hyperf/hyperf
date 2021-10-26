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
# source: grpc.proto

namespace GPBMetadata;

class Grpc
{
    public static $is_initialized = false;

    public static function initOnce()
    {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
            return;
        }
        $pool->internalAddGeneratedFile(hex2bin(
            '0aae010a0a677270632e70726f746f12046772706322200a04496e666f12' .
            '0a0a026964180120012805120c0a046e616d6518022001280922360a0955' .
            '7365725265706c79120f0a076d65737361676518012001280912180a0469' .
            '6e666f18022001280b320a2e677270632e496e666f32380a0c757365725f' .
            '7365727669636512280a0767657455736572120a2e677270632e496e666f' .
            '1a0f2e677270632e557365725265706c792200620670726f746f33'
        ));

        static::$is_initialized = true;
    }
}
