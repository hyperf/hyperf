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

namespace Hyperf\Di\Annotation;

use Hyperf\Di\Exception\AnnotationException;

interface MultipleAnnotationInterface extends AnnotationInterface
{
    public function className(): string;

    /**
     * @throws AnnotationException
     */
    public function insert(AnnotationInterface $annotation): static;

    /**
     * @return AnnotationInterface[]
     */
    public function toAnnotations(): array;
}
