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
# source: Grpc/grpc.proto

namespace HyperfTest\ResourceGrpc\Stubs\Grpc\GPBMetadata\Grpc;

use Google\Protobuf\Internal\DescriptorPool;

class Grpc
{
    public static $is_initialized = false;

    public static function initOnce()
    {
        $pool = DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
            return;
        }
        $pool->internalAddGeneratedFile(hex2bin(
            '0afd020a0f477270632f677270632e70726f746f12224879706572665465' .
            '73742e5265736f75726365477270632e53747562732e4772706322230a06' .
            '486955736572120c0a046e616d65180120012809120b0a03736578180220' .
            '01280522540a0748695265706c79120f0a076d6573736167651801200128' .
            '0912380a047573657218022001280b322a2e487970657266546573742e52' .
            '65736f75726365477270632e53747562732e477270632e48695573657222' .
            '560a08416c6c5265706c79120f0a076d6573736167651801200128091239' .
            '0a05757365727318022003280b322a2e487970657266546573742e526573' .
            '6f75726365477270632e53747562732e477270632e486955736572326b0a' .
            '02686912650a0873617948656c6c6f122a2e487970657266546573742e52' .
            '65736f75726365477270632e53747562732e477270632e4869557365721a' .
            '2b2e487970657266546573742e5265736f75726365477270632e53747562' .
            '732e477270632e48695265706c792200620670726f746f33'
        ), true);

        static::$is_initialized = true;
    }
}
