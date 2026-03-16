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

namespace Hyperf\Serializer;

use ArrayObject;
use Hyperf\Serializer\Contract\CacheableSupportsMethodInterface;
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

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return match ($type) {
            'int' => (int) $data,
            'string' => (string) $data,
            'float' => (float) $data,
            'bool' => (bool) $data,
            default => $data,
        };
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
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

    public function normalize(mixed $object, ?string $format = null, array $context = []): null|array|ArrayObject|bool|float|int|string
    {
        return $object;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return is_scalar($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => static::class === __CLASS__];
    }
}
