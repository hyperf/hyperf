<?php

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
