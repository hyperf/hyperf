<?php

namespace Hyperflex\Di\Annotation;

use Doctrine\Instantiator\Instantiator;
use Hyperflex\Di\Aop\ArroundInterface;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Aspect extends AbstractAnnotation
{
    public function collect(string $className, ?string $target): void
    {
        // @TODO Add order property.
        if (class_exists($className)) {
            // Create the aspect instance without invoking their constructor.
            $instantitor = new Instantiator();
            $instance = $instantitor->instantiate($className);
            switch ($instance) {
                case $instance instanceof ArroundInterface:
                    AspectCollector::setArround($className, $instance->classes, $instance->annotations);
                    break;
            }
        }
    }

}