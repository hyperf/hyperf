<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Utils\Serializer;

use Hyperf\Contract\NormalizerInterface;
use kuiper\reflection\TypeUtils;
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

    /**
     * {@inheritdoc}
     */
    public function normalize($object)
    {
        return $this->serializer->normalize($object);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class)
    {
        $type = TypeUtils::parse($class);
        if (TypeUtils::isPrimitive($type)
            || (TypeUtils::isArray($type) && TypeUtils::isPrimitive($type->getValueType()))) {
            return TypeUtils::sanitize($type, $data);
        }
        if (TypeUtils::isComposite($type)) {
            throw new \BadMethodCallException('Cannot denormalize composite type');
        }
        if (TypeUtils::isUnknown($type)) {
            return $data;
        }
        return $this->serializer->denormalize($data, $type->getName());
    }
}
