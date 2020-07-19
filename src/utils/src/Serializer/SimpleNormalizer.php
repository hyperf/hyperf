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

use Hyperf\Contract\NormalizerInterface;

class SimpleNormalizer implements NormalizerInterface
{
    public function normalize($object)
    {
        return $object;
    }

    public function denormalize($data, string $class)
    {
        switch ($class) {
            case 'int':
                return (int) $data;
            case 'string':
                return (string) $data;
            case 'float':
                return (float) $data;
            case 'array':
                return (array) $data;
            case 'bool':
                return (bool) $data;
            default:
                return $data;
        }
    }
}
