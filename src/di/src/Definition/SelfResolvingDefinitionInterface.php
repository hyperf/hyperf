<?php


namespace Hyperf\Di\Definition;


use Psr\Container\ContainerInterface;

interface SelfResolvingDefinitionInterface
{

    /**
     * Resolve the definition and return the resulting value.
     *
     * @return mixed
     */
    public function resolve(ContainerInterface $container);

    /**
     * Check if a definition can be resolved.
     */
    public function isResolvable(ContainerInterface $container): bool;

}