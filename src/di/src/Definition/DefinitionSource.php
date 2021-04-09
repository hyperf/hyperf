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
namespace Hyperf\Di\Definition;

use Hyperf\Di\ReflectionManager;
use ReflectionFunctionAbstract;
use function class_exists;
use function interface_exists;
use function is_callable;
use function is_string;
use function method_exists;

class DefinitionSource implements DefinitionSourceInterface
{
    /**
     * @var array
     */
    private $source;

    public function __construct(array $source)
    {
        $this->source = $this->normalizeSource($source);
    }

    /**
     * Returns the DI definition for the entry name.
     */
    public function getDefinition(string $name): ?DefinitionInterface
    {
        return $this->source[$name] ?? $this->source[$name] = $this->autowire($name);
    }

    /**
     * @return array definitions indexed by their name
     */
    public function getDefinitions(): array
    {
        return $this->source;
    }

    /**
     * @param array|callable|string $definition
     */
    public function addDefinition(string $name, $definition): self
    {
        $this->source[$name] = $this->normalizeDefinition($name, $definition);
        return $this;
    }

    public function clearDefinitions(): void
    {
        $this->source = [];
    }

    /**
     * Read the type-hinting from the parameters of the function.
     */
    private function getParametersDefinition(ReflectionFunctionAbstract $constructor): array
    {
        $parameters = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            // Skip optional parameters.
            if ($parameter->isOptional()) {
                continue;
            }

            $parameterClass = $parameter->getClass();

            if ($parameterClass) {
                $parameters[$index] = new Reference($parameterClass->getName());
            }
        }

        return $parameters;
    }

    /**
     * Normaliaze the user definition source to a standard definition souce.
     */
    private function normalizeSource(array $source): array
    {
        $definitions = [];
        foreach ($source as $identifier => $definition) {
            $normalizedDefinition = $this->normalizeDefinition($identifier, $definition);
            if (! is_null($normalizedDefinition)) {
                $definitions[$identifier] = $normalizedDefinition;
            }
        }
        return $definitions;
    }

    /**
     * @param array|callable|string $definition
     */
    private function normalizeDefinition(string $identifier, $definition): ?DefinitionInterface
    {
        if (is_string($definition) && class_exists($definition)) {
            if (method_exists($definition, '__invoke')) {
                return new FactoryDefinition($identifier, $definition, []);
            }
            return $this->autowire($identifier, new ObjectDefinition($identifier, $definition));
        }
        if (is_callable($definition)) {
            return new FactoryDefinition($identifier, $definition, []);
        }
        return null;
    }

    private function autowire(string $name, ObjectDefinition $definition = null): ?ObjectDefinition
    {
        $className = $definition ? $definition->getClassName() : $name;
        if (! class_exists($className) && ! interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ?: new ObjectDefinition($name);

        /**
         * Constructor.
         */
        $class = ReflectionManager::reflectClass($className);
        $constructor = $class->getConstructor();
        if ($constructor && $constructor->isPublic()) {
            $constructorInjection = new MethodInjection('__construct', $this->getParametersDefinition($constructor));
            $definition->completeConstructorInjection($constructorInjection);
        }

        return $definition;
    }
}
