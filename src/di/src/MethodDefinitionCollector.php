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

namespace Hyperf\Di;

class MethodDefinitionCollector extends MetadataCollector
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
}
