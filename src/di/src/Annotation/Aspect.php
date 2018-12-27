<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Annotation;

use Doctrine\Instantiator\Instantiator;
use Hyperf\Di\Aop\ArroundInterface;

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
