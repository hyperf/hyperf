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
# source: any.proto

namespace Hyperf\Nacos\Protobuf\GPBMetadata;

use Google\Protobuf\Internal\DescriptorPool;

class Any
{
    public static $is_initialized = false;

    public static function initOnce()
    {
        $pool = DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
            return;
        }
        $pool->internalAddGeneratedFile(
            '
ú
	any.protogoogle.protobuf"&
Any
type_url (	
value (B«
com.google.protobufBAnyProtoPZ%github.com/golang/protobuf/ptypes/any¢GPBªGoogle.Protobuf.WellKnownTypesÊHyperf\\Nacos\\Protobufâ!Hyperf\\Nacos\\Protobuf\\GPBMetadatabproto3',
            true
        );

        static::$is_initialized = true;
    }
}
