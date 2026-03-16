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

namespace Hyperf\JsonRpc;

use Hyperf\Contract\NormalizerInterface;

class JsonRpcNormalizer implements NormalizerInterface
{
    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function normalize($object)
    {
        return $this->normalizer->normalize($object);
    }

    public function denormalize($data, string $class)
    {
        return $this->normalizer->denormalize($data, $class);
    }
}
