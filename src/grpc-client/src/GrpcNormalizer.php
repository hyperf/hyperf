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

namespace Hyperf\GrpcClient;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Grpc\Parser;

class GrpcNormalizer implements NormalizerInterface
{
    public function normalize($object)
    {
        return Parser::serializeMessage($object);
    }

    public function denormalize($data, string $class)
    {
        return Parser::deserializeMessage([$class, 'decode'], $data);
    }
}
