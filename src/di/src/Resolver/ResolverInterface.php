<?php


namespace Hyperflex\Di\Resolver;


use Hyperflex\Di\Definition\DefinitionInterface;

interface ResolverInterface
{

    /**
     * Resolve a definition to a value.
     *
     * @param DefinitionInterface $definition Object that defines how the value should be obtained.
     * @param array $parameters Optional parameters to use to build the entry.
     * @throws InvalidDefinition If the definition cannot be resolved.
     * @return mixed Value obtained from the definition.
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []);

    /**
     * Check if a definition can be resolved.
     *
     * @param DefinitionInterface $definition Object that defines how the value should be obtained.
     * @param array $parameters Optional parameters to use to build the entry.
     * @return bool
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []): bool;

}