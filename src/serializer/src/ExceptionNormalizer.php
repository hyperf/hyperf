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

use Doctrine\Instantiator\Instantiator;
use Hyperf\Di\ReflectionManager;
use RuntimeException;
use Serializable;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

use function get_class;

class ExceptionNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    protected ?Instantiator $instantiator = null;

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (is_string($data)) {
            $ex = unserialize($data);
            if ($ex instanceof Throwable) {
                return $ex;
            }

            // Retry handle it if the exception not instanceof \Throwable.
            $data = $ex;
        }
        if (is_array($data) && isset($data['message'], $data['code'])) {
            try {
                $exception = $this->getInstantiator()->instantiate($type);
                foreach (['code', 'message', 'file', 'line'] as $attribute) {
                    if (isset($data[$attribute])) {
                        $property = ReflectionManager::reflectProperty($type, $attribute);
                        $property->setAccessible(true);
                        $property->setValue($exception, $data[$attribute]);
                    }
                }
                return $exception;
            } catch (\ReflectionException) {
                return new RuntimeException(sprintf(
                    'Bad data %s: %s',
                    $data['class'],
                    $data['message']
                ), $data['code']);
            } catch (\TypeError) {
                return new RuntimeException(sprintf(
                    'Uncaught data %s: %s',
                    $data['class'],
                    $data['message']
                ), $data['code']);
            }
        }

        return new RuntimeException('Bad data data: ' . json_encode($data));
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_a($type, Throwable::class, true);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if ($object instanceof Serializable) {
            return serialize($object);
        }
        /* @var \Throwable $object */
        return [
            'message' => $object->getMessage(),
            'code' => $object->getCode(),
            'file' => $object->getFile(),
            'line' => $object->getLine(),
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Throwable;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return get_class($this) === __CLASS__;
    }

    protected function getInstantiator(): Instantiator
    {
        if ($this->instantiator instanceof Instantiator) {
            return $this->instantiator;
        }

        return $this->instantiator = new Instantiator();
    }
}
