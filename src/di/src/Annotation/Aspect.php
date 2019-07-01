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

namespace Hyperf\Di\Annotation;

use Doctrine\Instantiator\Instantiator;
use Hyperf\Di\Aop\AroundInterface;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Aspect extends AbstractAnnotation
{
    /**
     * {@inheritdoc}
     */
    public function collectClass(string $className): void
    {
        // @TODO Add order property.
        if (class_exists($className)) {
            // Create the aspect instance without invoking their constructor.
            $instantitor = new Instantiator();
            $instance = $instantitor->instantiate($className);
            switch ($instance) {
                case $instance instanceof AroundInterface:
                    AspectCollector::setAround($className, $instance->classes, $instance->annotations);
                    break;
            }
        }
    }
}
