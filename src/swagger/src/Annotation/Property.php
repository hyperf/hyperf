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
use OpenApi\Attributes\AdditionalProperties;
use OpenApi\Attributes\Discriminator;
use OpenApi\Attributes\ExternalDocumentation;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Xml;
use OpenApi\Generator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class Property extends \OpenApi\Attributes\Property
{
    public function __construct(
        ?string $property = null,
        null|object|string $ref = null,
        ?string $schema = null,
        ?string $title = null,
        ?string $description = null,
        ?int $maxProperties = null,
        ?int $minProperties = null,
        ?array $required = null,
        ?array $properties = null,
        ?string $type = null,
        ?string $format = null,
        ?Items $items = null,
        ?string $collectionFormat = null,
        mixed $default = Generator::UNDEFINED,
        $maximum = null,
        ?bool $exclusiveMaximum = null,
        $minimum = null,
        ?bool $exclusiveMinimum = null,
        ?int $maxLength = null,
        ?int $minLength = null,
        ?int $maxItems = null,
        ?int $minItems = null,
        ?bool $uniqueItems = null,
        ?string $pattern = null,
        null|array|string $enum = null,
        ?Discriminator $discriminator = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        ?Xml $xml = null,
        ?ExternalDocumentation $externalDocs = null,
        mixed $example = Generator::UNDEFINED,
        ?bool $nullable = null,
        ?bool $deprecated = null,
        ?array $allOf = null,
        ?array $anyOf = null,
        ?array $oneOf = null,
        null|AdditionalProperties|bool $additionalProperties = null,
        ?array $x = null,
        ?array $attachables = null,
        public mixed $rules = null,
        public mixed $attribute = null
    ) {
        parent::__construct(
            property: $property,
            ref: $ref,
            schema: $schema,
            title: $title,
            description: $description,
            maxProperties: $maxProperties,
            minProperties: $minProperties,
            required: $required,
            properties: $properties,
            type: $type,
            format: $format,
            items: $items,
            collectionFormat: $collectionFormat,
            default: $default,
            maximum: $maximum,
            exclusiveMaximum: $exclusiveMaximum,
            minimum: $minimum,
            exclusiveMinimum: $exclusiveMinimum,
            maxLength: $maxLength,
            minLength: $minLength,
            maxItems: $maxItems,
            minItems: $minItems,
            uniqueItems: $uniqueItems,
            pattern: $pattern,
            enum: $enum,
            discriminator: $discriminator,
            readOnly: $readOnly,
            writeOnly: $writeOnly,
            xml: $xml,
            externalDocs: $externalDocs,
            example: $example,
            nullable: $nullable,
            deprecated: $deprecated,
            allOf: $allOf,
            anyOf: $anyOf,
            oneOf: $oneOf,
            additionalProperties: $additionalProperties,
            x: $x,
            attachables: $attachables
        );
    }
}
