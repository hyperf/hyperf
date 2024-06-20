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

namespace Hyperf\Di\Resolver;

use Hyperf\Di\Definition\DefinitionInterface;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Di\Exception\DependencyException;
use Hyperf\Di\Exception\InvalidDefinitionException;
use Hyperf\Di\ReflectionManager;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ObjectResolver implements ResolverInterface
{
    private ParameterResolver $parameterResolver;

    /**
     * ObjectResolver constructor.
     */
    public function __construct(private ContainerInterface $container, private ResolverInterface $definitionResolver)
    {
        $this->parameterResolver = new ParameterResolver($this->definitionResolver);
    }

    /**
     * Resolve a definition to a value.
     *
     * @param DefinitionInterface $definition object that defines how the value should be obtained
     * @param array $parameters optional parameters to use to build the entry
     * @return mixed value obtained from the definition
     * @throws InvalidDefinitionException
     * @throws DependencyException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        if (! $definition instanceof ObjectDefinition) {
            throw InvalidDefinitionException::create(
                $definition,
                sprintf('Entry "%s" cannot be resolved: the class is not instanceof ObjectDefinition', $definition->getName())
            );
        }
        return $this->createInstance($definition, $parameters);
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param ObjectDefinition $definition object that defines how the value should be obtained
     * @param array $parameters optional parameters to use to build the entry
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []): bool
    {
        return $definition->isInstantiable();
    }

    private function createInstance(ObjectDefinition $definition, array $parameters)
    {
        // Check that the class is instantiable
        if (! $definition->isInstantiable()) {
            // Check that the class exists
            if (! $definition->isClassExists()) {
                throw InvalidDefinitionException::create($definition, sprintf('Entry "%s" cannot be resolved: the class doesn\'t exist', $definition->getName()));
            }

            throw InvalidDefinitionException::create($definition, sprintf('Entry "%s" cannot be resolved: the class is not instantiable', $definition->getName()));
        }

        $classReflection = null;
        try {
            $className = $definition->getClassName();
            $classReflection = ReflectionManager::reflectClass($className);
            $constructorInjection = $definition->getConstructorInjection();

            $args = $this->parameterResolver->resolveParameters($constructorInjection, $classReflection->getConstructor(), $parameters);
            $object = new $className(...$args);
        } catch (NotFoundExceptionInterface $e) {
            throw new DependencyException(sprintf('Error while injecting dependencies into %s: %s', $classReflection ? $classReflection->getName() : '', $e->getMessage()), 0, $e);
        } catch (InvalidDefinitionException $e) {
            throw InvalidDefinitionException::create($definition, sprintf('Entry "%s" cannot be resolved: %s', $definition->getName(), $e->getMessage()));
        }
        return $object;
    }
}
