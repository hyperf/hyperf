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

use Hyperf\Di\MetadataCollector;

class AspectCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function setArround(string $aspect, array $classes, array $annotations)
    {
        $savedClasses = static::get('classes.' . $aspect, []);
        $savedAnnotations = static::get('annotations.' . $aspect, []);
        static::set('classes.' . $aspect, array_replace($savedClasses, $classes));
        static::set('annotations.' . $aspect, array_replace($savedAnnotations, $annotations));
    }
}
