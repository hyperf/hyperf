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
# source: Grpc/grpc.proto

namespace HyperfTest\Resource\Stubs\Grpc\GPBMetadata\Grpc;

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
            '0ae9020a0f477270632f677270632e70726f746f121e4879706572665465' .
            '73742e5265736f757263652e53747562732e4772706322230a0648695573' .
            '6572120c0a046e616d65180120012809120b0a0373657818022001280522' .
            '500a0748695265706c79120f0a076d65737361676518012001280912340a' .
            '047573657218022001280b32262e487970657266546573742e5265736f75' .
            '7263652e53747562732e477270632e48695573657222520a08416c6c5265' .
            '706c79120f0a076d65737361676518012001280912350a05757365727318' .
            '022003280b32262e487970657266546573742e5265736f757263652e5374' .
            '7562732e477270632e48695573657232630a026869125d0a087361794865' .
            '6c6c6f12262e487970657266546573742e5265736f757263652e53747562' .
            '732e477270632e4869557365721a272e487970657266546573742e526573' .
            '6f757263652e53747562732e477270632e48695265706c79220062067072' .
            '6f746f33'
        ), true);

        static::$is_initialized = true;
    }
}
