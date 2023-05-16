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
use OpenApi\Attributes\XmlContent;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class RequestBody extends \OpenApi\Attributes\RequestBody implements AnnotationInterface
{
    use AnnotationTrait;

    public mixed $_content = null;

    public function __construct(
        object|string|null $ref = null,
        ?string $request = null,
        ?string $description = null,
        ?bool $required = null,
        JsonContent|array|Attachable|XmlContent|null $content = null,
        ?array $x = null,
        ?array $attachables = null
    ) {
        parent::__construct($ref, $request, $description, $required, $content, $x, $attachables);

        $this->_content = $content;
    }
}
