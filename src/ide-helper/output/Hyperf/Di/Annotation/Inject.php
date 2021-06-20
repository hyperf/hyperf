<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Di\Annotation;

use Attribute;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Di\ReflectionManager;
use Hyperf\Utils\CodeGen\PhpDocReaderManager;
use PhpDocReader\AnnotationException as DocReaderAnnotationException;
/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject extends AbstractAnnotation
{
    public function __construct($value, $required, $lazy)
    {
    }
}