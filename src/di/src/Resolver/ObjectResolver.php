<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Resolver;

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionInterface;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Di\Definition\PropertyInjection;
use Hyperf\Di\Definition\Reference;
use Hyperf\Di\Exception\DependencyException;
use Hyperf\Di\Exception\InvalidDefinitionException;
use Hyperf\Di\ReflectionManager;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ObjectResolver implements ResolverInterface
{
    private $proxyFactory;

    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @var ResolverInterface
     */
    private $definitionResolver;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ObjectResolver constructor.
     *
     * @param Container $container
     * @param ResolverInterface $definitionResolver
     */
    public function __construct(ContainerInterface $container, ResolverInterface $definitionResolver)
    {
        $this->container = $container;
        $this->definitionResolver = $definitionResolver;
        $this->proxyFactory = $container->getProxyFactory();
        $this->parameterResolver = new ParameterResolver($definitionResolver);
    }

    /**
     * Resolve a definition to a value.
     *
     * @param DefinitionInterface $definition Object that defines how the value should be obtained.
     * @param array $parameters Optional parameters to use to build the entry.
     * @return mixed Value obtained from the definition.
     * @throws DependencyException
     * @throws InvalidDefinitionException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        return $this->createInstance($definition, $parameters);
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param ObjectDefinition $definition Object that defines how the value should be obtained.
     * @param array $parameters Optional parameters to use to build the entry.
     * @return bool
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []): bool
    {
        return $definition->isInstantiable();
    }

    protected function injectMethodsAndProperties($object, ObjectDefinition $objectDefinition)
    {
        // Property injections
        foreach ($objectDefinition->getPropertyInjections() as $propertyInjection) {
            $this->injectProperty($object, $propertyInjection);
        }
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
            if ($definition->isNeedProxy()) {
                $definition = $this->proxyFactory->createProxyDefinition($definition);
                $className = $definition->getProxyClassName();
            }
            $classReflection = ReflectionManager::reflectClass($className);
            $constructorInjection = $definition->getConstructorInjection();

            $args = $this->parameterResolver->resolveParameters($constructorInjection, $classReflection->getConstructor(), $parameters);
            $object = new $className(...$args);
            $this->injectMethodsAndProperties($object, $definition);
        } catch (NotFoundExceptionInterface $e) {
            throw new DependencyException(sprintf('Error while injecting dependencies into %s: %s', $classReflection ? $classReflection->getName() : '', $e->getMessage()), 0, $e);
        } catch (InvalidDefinitionException $e) {
            throw InvalidDefinitionException::create($definition, sprintf('Entry "%s" cannot be resolved: %s', $definition->getName(), $e->getMessage()));
        }
        return $object;
    }

    private function injectProperty($object, PropertyInjection $propertyInjection)
    {
        $property = ReflectionManager::reflectProperty(get_class($object), $propertyInjection->getPropertyName());
        if ($property->isStatic()) {
            return;
        }
        if (! $property->isPublic()) {
            $property->setAccessible(true);
        }
        if (! $propertyInjection->getValue() instanceof Reference) {
            return;
        }
        /** @var Reference $reference */
        $reference = $propertyInjection->getValue();
        $property->setValue($object, $this->container->get($reference->getTargetEntryName()));
    }
}
