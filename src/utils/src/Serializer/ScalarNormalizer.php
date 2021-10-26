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
namespace Hyperf\Utils\Serializer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function get_class;
use function is_scalar;

class ScalarNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function hasCacheableSupportsMethod(): bool
    {
        return get_class($this) === __CLASS__;
    }

    public function denormalize($data, string $class, string $format = null, array $context = [])
    {
        switch ($class) {
            case 'int':
                return (int) $data;
            case 'string':
                return (string) $data;
            case 'float':
                return (float) $data;
            case 'bool':
                return (bool) $data;
            default:
                return $data;
        }
    }

    public function supportsDenormalization($data, $type, string $format = null)
    {
        return in_array($type, [
            'int',
            'string',
            'float',
            'bool',
            'mixed',
            'array', // TODO: Symfony\Component\Serializer\Normalizer\ArrayDenormalizer not support array, so it denormalized in ScalarNormalizer.
        ]);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        return $object;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return is_scalar($data);
    }
}
