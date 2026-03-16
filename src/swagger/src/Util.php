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

namespace Hyperf\Swagger;

use Hyperf\Di\Annotation\MultipleAnnotation;

class Util
{
    public static function findAnnotations(?array $annotations, string $class): array
    {
        $result = [];
        foreach ((array) $annotations as $annotation) {
            if ($annotation instanceof $class) {
                $result[] = $annotation;
            }

            if ($annotation instanceof MultipleAnnotation) {
                if ($annotation->className() === $class) {
                    $result = array_merge($result, $annotation->toAnnotations());
                }
            }
        }

        return $result;
    }
}
