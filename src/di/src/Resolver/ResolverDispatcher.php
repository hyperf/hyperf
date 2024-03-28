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
use Hyperf\Di\Definition\FactoryDefinition;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Di\Definition\SelfResolvingDefinitionInterface;
use Hyperf\Di\Exception\InvalidDefinitionException;
use Psr\Container\ContainerInterface;
use RuntimeException;

class ResolverDispatcher implements ResolverInterface
{
    protected ?ObjectResolver $objectResolver = null;

    protected ?FactoryResolver $factoryResolver = null;

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Resolve a definition to a value.
     *
     * @param DefinitionInterface $definition object that defines how the value should be obtained
     * @param array $parameters optional parameters to use to build the entry
     * @return mixed value obtained from the definition
     * @throws InvalidDefinitionException if the definition cannot be resolved
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        if ($definition instanceof SelfResolvingDefinitionInterface) {
            return $definition->resolve($this->container);
        }

        $guard = DepthGuard::getInstance();

        return $guard->call(
            $definition->getName(),
            fn () => $this->getDefinitionResolver($definition)->resolve($definition, $parameters)
        );
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param DefinitionInterface $definition object that defines how the value should be obtained
     * @param array $parameters optional parameters to use to build the entry
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []): bool
    {
        if ($definition instanceof SelfResolvingDefinitionInterface) {
            return $definition->isResolvable($this->container);
        }

        $guard = DepthGuard::getInstance();

        return $guard->call(
            $definition->getName(),
            fn () => $this->getDefinitionResolver($definition)->isResolvable($definition, $parameters)
        );
    }

    /**
     * Returns a resolver capable of handling the given definition.
     *
     * @throws RuntimeException no definition resolver was found for this type of definition
     */
    private function getDefinitionResolver(DefinitionInterface $definition): ResolverInterface
    {
        return match (true) {
            $definition instanceof ObjectDefinition => $this->objectResolver ??= new ObjectResolver($this->container, $this),
            $definition instanceof FactoryDefinition => $this->factoryResolver ??= new FactoryResolver($this->container, $this),
            default => throw new RuntimeException('No definition resolver was configured for definition of type ' . get_class($definition)),
        };
    }
}
