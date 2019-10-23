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

class MethodDefinitionCollector extends MetadataCollector implements MethodDefinitionCollectorInterface
{
    /**
     * @var array
     */
    protected static $container = [];

    /**
     * Get the method definition from metadata container,
     * If the metadata not exist in container, then will
     * parse it and save into container, and then return it.
     */
    public static function getOrParse(string $class, string $method): array
    {
        $key = $class . '::' . $method;
        if (static::has($key)) {
            return static::get($key);
        }
        $parameters = ReflectionManager::reflectMethod($class, $method)->getParameters();
        $definitions = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType()->getName();
            switch ($type) {
                case 'int':
                case 'float':
                case 'string':
                case 'array':
                case 'bool':
                    $definition = [
                        'type' => $type,
                        'name' => $parameter->getName(),
                        'ref' => '',
                        'allowsNull' => $parameter->allowsNull(),
                    ];
                    if ($parameter->isDefaultValueAvailable()) {
                        $definition['defaultValue'] = $parameter->getDefaultValue();
                    }
                    $definitions[] = $definition;
                    break;
                default:
                    // Object
                    $definitions[] = [
                        'type' => 'object',
                        'name' => $parameter->getName(),
                        'ref' => $parameter->getClass()->getName() ?? null,
                        'allowsNull' => $parameter->allowsNull(),
                    ];
                    break;
            }
        }
        static::set($key, $definitions);
        return $definitions;
    }

    public function getParameters(string $class, string $method): array
    {
        $key = $class . '::' . $method . '@params';
        if (static::has($key)) {
            return static::get($key);
        }
        $parameters = ReflectionManager::reflectClass($class)->getMethod($method)->getParameters();
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

        static::set($key, $definitions);
        return $definitions;
    }

    public function getReturnType(string $class, string $method): ReflectionType
    {
        $key = $class . '::' . $method . '@return';
        if (static::has($key)) {
            return static::get($key);
        }
        $returnType = ReflectionManager::reflectClass($class)->getMethod($method)->getReturnType();
        $type = $this->createType('', $returnType, $returnType ? $returnType->allowsNull() : true);
        static::set($key, $type);
        return $type;
    }

    private function createType($name, ?\ReflectionType $type, $allowsNull, $hasDefault = false, $defaultValue = null)
    {
        return new ReflectionType($type ? $type->getName() : 'mixed', $allowsNull, [
            'defaultValueAvailable' => $hasDefault,
            'defaultValue' => $defaultValue,
            'name' => $name,
        ]);
    }
}
