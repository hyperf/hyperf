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
namespace Hyperf\Di;

abstract class AbstractCallableDefinitionCollector extends MetadataCollector
{
    /**
     * @param array<\ReflectionParameter> $parameters
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
                $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null
            );
        }
        return $definitions;
    }

    protected function createType($name, ?\ReflectionType $type, $allowsNull, $hasDefault = false, $defaultValue = null)
    {
        return new ReflectionType($type ? $type->getName() : 'mixed', $allowsNull, [
            'defaultValueAvailable' => $hasDefault,
            'defaultValue' => $defaultValue,
            'name' => $name,
        ]);
    }
}
