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

    /**
     * Denormalizer constructor.
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     * @param mixed $object
     * @return array|bool|float|int|string
     */
    public function normalize($object)
    {
        return $this->serializer->normalize($object);
    }

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed $data Data to restore
     * @param string $class The expected class to instantiate
     * @return object
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
        return $this->serializer->denormalize($data, $type->getName());
    }
}
