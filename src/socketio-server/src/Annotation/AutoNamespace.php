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
namespace Hyperf\SocketIOServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\SocketIOServer\Collector\EventAnnotationCollector;
use Roave\BetterReflection\Reflection\Adapter\ReflectionMethod;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class AutoNamespace extends AbstractAnnotation
{
    public function collectClass(string $className): void
    {
        $methods = ReflectionManager::reflectClass($className)->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $target = $method->getName();
            EventAnnotationCollector::collectEvent($className, $target, new Event(['value' => $target]));
        }
        parent::collectClass($className);
    }
}
