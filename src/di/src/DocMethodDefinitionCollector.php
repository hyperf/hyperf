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

use kuiper\docReader\DocReaderInterface;

class DocMethodDefinitionCollector extends MetadataCollector implements MethodDefinitionCollectorInterface
{
    /**
     * @var DocReaderInterface
     */
    private $docReader;

    public function __construct(DocReaderInterface $docReader)
    {
        $this->docReader = $docReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(string $class, string $method): array
    {
        $key = $class . '::' . $method;
        if (static::has($key)) {
            return static::get($key);
        }
        $reflectionMethod = ReflectionManager::reflectClass($class)->getMethod($method);
        $parameters = $this->docReader->getParameterTypes($reflectionMethod);
        $definitions = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $definitions[] = $this->createType(
                $parameter->getName(),
                $parameters[$parameter->getName()],
                $parameter->allowsNull(),
                $parameter->isDefaultValueAvailable(),
                $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null
            );
        }

        static::set($key, $definitions);
        return $definitions;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnType(string $class, string $method): ReflectionType
    {
        $key = $class . '::' . $method . '@return';
        if (static::has($key)) {
            return static::get($key);
        }
        $returnType = $this->docReader->getReturnType(ReflectionManager::reflectClass($class)->getMethod($method));
        $type = $this->createType('', $returnType, $returnType->allowsNull());
        static::set($key, $type);
        return $type;
    }

    /**
     * @param string $name
     * @param \kuiper\reflection\ReflectionTypeInterface $type
     * @param bool $allowsNull
     * @param bool $hasDefault
     * @param mixed $defaultValue
     * @return ReflectionType
     */
    private function createType($name, $type, $allowsNull, $hasDefault = false, $defaultValue = null)
    {
        return new ReflectionType((string) $type, $allowsNull, [
            'defaultValueAvailable' => $hasDefault,
            'defaultValue' => $defaultValue,
            'name' => $name,
        ]);
    }
}
