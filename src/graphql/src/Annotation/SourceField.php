<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\GraphQL\Annotation;

use Hyperf\Di\Annotation\AnnotationInterface;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *     @Attribute("name", type="string"),
 *     @Attribute("logged", type="bool"),
 *     @Attribute("right", type="TheCodingMachine\GraphQLite\Annotations\Right"),
 *     @Attribute("outputType", type="string"),
 *     @Attribute("isId", type="bool"),
 *     @Attribute("failWith", type="mixed"),
 * })
 */
class SourceField extends \TheCodingMachine\GraphQLite\Annotations\SourceField implements AnnotationInterface
{
    use AnnotationTrait;
}
