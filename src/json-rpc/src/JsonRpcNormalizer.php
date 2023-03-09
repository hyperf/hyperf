<?php

namespace Hyperf\JsonRpc;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Grpc\Parser;
use Hyperf\Utils\ApplicationContext;

class JsonRpcNormalizer implements NormalizerInterface
{
    public function normalize($object)
    {
        return ApplicationContext::getContainer()->get(NormalizerInterface::class)->normalize($object);
    }

    public function denormalize($data, string $class)
    {
        return ApplicationContext::getContainer()->get(NormalizerInterface::class)->denormalize($data,$class);
    }

}
