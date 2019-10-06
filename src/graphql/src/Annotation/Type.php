<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GraphQL\Annotation;

use Hyperf\Di\Annotation\AnnotationInterface;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *     @Attribute("class", type="string"),
 * })
 */
class Type extends \TheCodingMachine\GraphQLite\Annotations\Type implements AnnotationInterface
{
    use AnnotationTrait;
}
