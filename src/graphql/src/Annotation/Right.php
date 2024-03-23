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

namespace Hyperf\GraphQL\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AnnotationInterface;

/**
 * @Annotation
 * @Target({"ANNOTATION", "METHOD"})
 * @Attributes({
 *     @Attribute("name", type="string"),
 * })
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Right extends \TheCodingMachine\GraphQLite\Annotations\Right implements AnnotationInterface
{
    use AnnotationTrait;
}
