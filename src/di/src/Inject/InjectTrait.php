<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Di\Inject;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Utils\ApplicationContext;

trait InjectTrait
{
    public function __injectProperties(string $className)
    {
        // Inject the properties of current class
        $this->__inject(AnnotationCollector::get($className) ?? []);
        // Inject the properties of parent class
        $reflectionClass = BetterReflectionManager::reflectClass($className);
        $parentClassNames = $reflectionClass->getParentClassNames();
        foreach ($parentClassNames ?? [] as $parentClassName) {
            $this->__inject(AnnotationCollector::get($parentClassName) ?? []);
        }
        // Inject the properties of traits
        $traitNames = $reflectionClass->getTraitNames();
        foreach ($traitNames ?? [] as $traitName) {
            $this->__inject(AnnotationCollector::get($traitName) ?? []);
        }
    }

    private function __inject(array $propertiesMetadata)
    {
        foreach ($propertiesMetadata['_p'] ?? [] as $property => $propertyMetadata) {
            foreach ($propertyMetadata as $metadata) {
                if ($metadata instanceof Inject) {
                    try {
                        $this->{$property} = ApplicationContext::getContainer()->get($metadata->value);
                    } catch (\Throwable $throwable) {
                        if ($metadata->required) {
                            throw $throwable;
                        }
                    }
                }
            }
        }
    }
}
