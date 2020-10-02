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
use Symfony\Component\Serializer\Serializer;

class SymfonyNormalizer implements NormalizerInterface
{
    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function normalize($object)
    {
        return $this->serializer->normalize($object);
    }

    public function denormalize($data, string $class)
    {
        return $this->serializer->denormalize($data, $class);
    }
}
