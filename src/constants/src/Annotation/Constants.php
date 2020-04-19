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
namespace Hyperf\Constants\Annotation;

use Hyperf\Constants\AnnotationReader;
use Hyperf\Constants\ConstantsCollector;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Constants extends AbstractAnnotation
{
    public function collectClass(string $className): void
    {
        $reader = new AnnotationReader();

        $ref = new \ReflectionClass($className);
        $class = $ref->getParentClass()->getName();

        if ($class !== AbstractConstants::class) {
            $className = $class;
        }
        $classConstants = $ref->getReflectionConstants();
        $data = $reader->getAnnotations($classConstants);

        $data = $data + (ConstantsCollector::get($className) ?? []);
        ConstantsCollector::set($className, $data);
    }
}
