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

namespace Hyperf\GraphQL\Annotation;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\GraphQL\ClassCollector;
use ReflectionProperty;

trait AnnotationTrait
{
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
        ClassCollector::collect($className);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, static::class, $this);
        ClassCollector::collect($className);
    }

    public function collectProperty(string $className, ?string $target): void
    {
        AnnotationCollector::collectProperty($className, $target, static::class, $this);
        ClassCollector::collect($className);
    }

    protected function bindMainProperty(string $key, array $value)
    {
        if (isset($value['value'])) {
            $this->{$key} = $value['value'];
        }
    }
}
