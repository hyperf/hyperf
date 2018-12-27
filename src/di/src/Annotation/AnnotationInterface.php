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

interface AnnotationInterface
{
    /**
     * @return string Collect the annotation metadata to a container that you wants.
     */
    public function collect(string $className, ?string $target): void;
}
