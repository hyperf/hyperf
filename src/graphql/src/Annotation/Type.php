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
 * @Target({"CLASS"})
 * @Attributes({
 *     @Attribute("class", type="string"),
 * })
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Type extends \TheCodingMachine\GraphQLite\Annotations\Type implements AnnotationInterface
{
    use AnnotationTrait;
}
