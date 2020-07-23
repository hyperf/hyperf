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
namespace Hyperf\Di\Annotation;

use Hyperf\Di\ReflectionManager;
use Hyperf\Utils\Contracts\Arrayable;
use ReflectionProperty;

abstract class AbstractAnnotation implements AnnotationInterface, Arrayable
{
    public function __construct($value = null)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $val;
                }
            }
        }
    }

    public function toArray(): array
    {
        $properties = ReflectionManager::reflectClass(static::class)->getProperties(ReflectionProperty::IS_PUBLIC);
        $result = [];
        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getValue($this);
        }
        return $result;
    }

    public function collectClass(string $className): void
    {
        AnnotationCollector::collectClass($className, static::class, $this);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, static::class, $this);
    }

    public function collectProperty(string $className, ?string $target): void
    {
        AnnotationCollector::collectProperty($className, $target, static::class, $this);
    }

    protected function bindMainProperty(string $key, ?array $value)
    {
        if (isset($value['value'])) {
            $this->{$key} = $value['value'];
        }
    }
}
