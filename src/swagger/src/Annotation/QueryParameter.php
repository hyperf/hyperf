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
use OpenApi\Attributes\Attachable;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Schema;
use OpenApi\Attributes\XmlContent;
use OpenApi\Generator;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class QueryParameter extends \OpenApi\Attributes\QueryParameter implements AnnotationInterface
{
    use MultipleAnnotationTrait;

    public function __construct(
        ?string $parameter = null,
        ?string $name = null,
        ?string $description = null,
        ?string $in = null,
        ?bool $required = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null,
        object|string|null $ref = null,
        ?Schema $schema = null,
        mixed $example = Generator::UNDEFINED,
        ?array $examples = null,
        JsonContent|array|Attachable|XmlContent|null $content = null,
        ?string $style = null,
        ?bool $explode = null,
        ?bool $allowReserved = null,
        ?array $spaceDelimited = null,
        ?array $pipeDelimited = null,
        ?array $x = null,
        ?array $attachables = null,
        public mixed $rules = null,
    ) {
        parent::__construct(
            $parameter,
            $name,
            $description,
            $in,
            $required,
            $deprecated,
            $allowEmptyValue,
            $ref,
            $schema,
            $example,
            $examples,
            $content,
            $style,
            $explode,
            $allowReserved,
            $spaceDelimited,
            $pipeDelimited,
            $x,
            $attachables
        );
    }
}
