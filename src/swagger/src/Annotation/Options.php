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
namespace Hyperf\Swagger\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class Options extends \OpenApi\Attributes\Options implements AnnotationInterface
{
    use MultipleAnnotationTrait;
}
