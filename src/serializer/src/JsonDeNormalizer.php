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

use Hyperf\Contract\NormalizerInterface;

class JsonDeNormalizer implements NormalizerInterface
{
    public function normalize($object)
    {
        return $object;
    }

    public function denormalize($data, string $class)
    {
        return match ($class) {
            'int' => (int) $data,
            'string' => (string) $data,
            'float' => (float) $data,
            'array' => (array) $data,
            'bool' => (bool) $data,
            'mixed' => $data,
            default => $this->from($data, $class),
        };
    }

    private function from(mixed $data, string $class): mixed
    {
        if (method_exists($class, 'jsonDeSerialize')) {
            return $class::jsonDeSerialize($data);
        }

        return $data;
    }
}
