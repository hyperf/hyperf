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

namespace Hyperf\Constants\Annotation;

use Attribute;
use Hyperf\Constants\AnnotationReader;
use Hyperf\Constants\ConstantsCollector;
use Hyperf\Di\Annotation\AbstractAnnotation;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class Constants extends AbstractAnnotation
{
    public function collectClass(string $className): void
    {
        $reader = new AnnotationReader();

        $ref = new ReflectionClass($className);
        $classConstants = $ref->getReflectionConstants();
        $data = $reader->getAnnotations($classConstants);

        ConstantsCollector::set($className, $data);
    }
}
