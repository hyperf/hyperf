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
# source: route_guide.proto

namespace GPBMetadata;

use Google\Protobuf\Internal\DescriptorPool;

class RouteGuide
{
    public static $is_initialized = false;

    public static function initOnce()
    {
        $pool = DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
            return;
        }
        $pool->internalAddGeneratedFile(hex2bin(
            '0ac5050a11726f7574655f67756964652e70726f746f120a726f75746567' .
            '75696465222c0a05506f696e7412100a086c617469747564651801200128' .
            '0512110a096c6f6e67697475646518022001280522490a0952656374616e' .
            '676c65121d0a026c6f18012001280b32112e726f75746567756964652e50' .
            '6f696e74121d0a02686918022001280b32112e726f75746567756964652e' .
            '506f696e74223c0a0746656174757265120c0a046e616d65180120012809' .
            '12230a086c6f636174696f6e18022001280b32112e726f75746567756964' .
            '652e506f696e7422410a09526f7574654e6f746512230a086c6f63617469' .
            '6f6e18012001280b32112e726f75746567756964652e506f696e74120f0a' .
            '076d65737361676518022001280922620a0c526f75746553756d6d617279' .
            '12130a0b706f696e745f636f756e7418012001280512150a0d6665617475' .
            '72655f636f756e7418022001280512100a0864697374616e636518032001' .
            '280512140a0c656c61707365645f74696d651804200128053285020a0a52' .
            '6f757465477569646512360a0a4765744665617475726512112e726f7574' .
            '6567756964652e506f696e741a132e726f75746567756964652e46656174' .
            '7572652200123e0a0c4c697374466561747572657312152e726f75746567' .
            '756964652e52656374616e676c651a132e726f75746567756964652e4665' .
            '617475726522003001123e0a0b5265636f7264526f75746512112e726f75' .
            '746567756964652e506f696e741a182e726f75746567756964652e526f75' .
            '746553756d6d61727922002801123f0a09526f7574654368617412152e72' .
            '6f75746567756964652e526f7574654e6f74651a152e726f757465677569' .
            '64652e526f7574654e6f746522002801300142360a1b696f2e677270632e' .
            '6578616d706c65732e726f7574656775696465420f526f75746547756964' .
            '6550726f746f5001a20203525447620670726f746f33'
        ));

        static::$is_initialized = true;
    }
}
