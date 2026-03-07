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

namespace Hyperf\Di;

use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

abstract class AbstractCallableDefinitionCollector extends MetadataCollector
{
    /**
     * @param array<ReflectionParameter> $parameters
     */
    protected function getDefinitionsFromParameters(array $parameters): array
    {
        $definitions = [];
        foreach ($parameters as $parameter) {
            $definitions[] = $this->createType(
                $parameter->getName(),
                $parameter->getType(),
                $parameter->allowsNull(),
                $parameter->isDefaultValueAvailable(),
                $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                $parameter->getAttributes()
            );
        }
        return $definitions;
    }

    /**
     * @param ReflectionAttribute[] $attributes
     */
    protected function createType(string $name, ?\ReflectionType $type, bool $allowsNull, bool $hasDefault = false, mixed $defaultValue = null, array $attributes = []): ReflectionType
    {
        // TODO: Support ReflectionUnionType.
        $typeName = match (true) {
            $type instanceof ReflectionNamedType => $type->getName(),
            $type instanceof ReflectionUnionType => $type->getTypes()[0]->getName(),
            default => 'mixed'
        };
        return new ReflectionType($typeName, $allowsNull, [
            'defaultValueAvailable' => $hasDefault,
            'defaultValue' => $defaultValue,
            'name' => $name,
            'attributes' => $attributes,
        ]);
    }
}
