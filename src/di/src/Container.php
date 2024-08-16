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

namespace Hyperf\Di;

use Hyperf\Contract\ContainerInterface as HyperfContainerInterface;
use Hyperf\Di\Definition\DefinitionInterface;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Di\Exception\InvalidArgumentException;
use Hyperf\Di\Exception\NotFoundException;
use Hyperf\Di\Resolver\ResolverDispatcher;
use Psr\Container\ContainerInterface as PsrContainerInterface;

class Container implements HyperfContainerInterface
{
    /**
     * Map of entries that are already resolved.
     */
    protected array $resolvedEntries;

    /**
     * Map of definitions that are already fetched (local cache).
     */
    protected array $fetchedDefinitions = [];

    protected Resolver\ResolverInterface $definitionResolver;

    /**
     * Container constructor.
     */
    public function __construct(protected Definition\DefinitionSourceInterface $definitionSource)
    {
        $this->definitionResolver = new ResolverDispatcher($this);
        // Auto-register the container.
        $this->resolvedEntries = [
            self::class => $this,
            PsrContainerInterface::class => $this,
            HyperfContainerInterface::class => $this,
        ];
    }

    /**
     * @internal
     */
    public function make(string $name, array $parameters = [])
    {
        $definition = $this->getDefinition($name);

        if (! $definition) {
            throw new NotFoundException("No entry or class found for '{$name}'");
        }

        return $this->resolveDefinition($definition, $parameters);
    }

    /**
     * @internal
     * @param mixed $entry
     */
    public function set(string $name, $entry): void
    {
        $this->resolvedEntries[$name] = $entry;
    }

    /**
     * @internal
     */
    public function unbind(string $name): void
    {
        if ($this->has($name)) {
            unset($this->resolvedEntries[$name]);
        }
    }

    /**
     * @internal
     * @param mixed $definition
     */
    public function define(string $name, $definition): void
    {
        $this->setDefinition($name, $definition);
    }

    /**
     * @internal
     * @param mixed $id
     */
    public function get($id)
    {
        // If the entry is already resolved we return it
        if (isset($this->resolvedEntries[$id]) || array_key_exists($id, $this->resolvedEntries)) {
            return $this->resolvedEntries[$id];
        }
        return $this->resolvedEntries[$id] = $this->make($id);
    }

    /**
     * @internal
     * @param mixed $id
     */
    public function has($id): bool
    {
        if (! is_string($id)) {
            throw new InvalidArgumentException(sprintf('The name parameter must be of type string, %s given', is_object($id) ? get_class($id) : gettype($id)));
        }

        if (array_key_exists($id, $this->resolvedEntries)) {
            return true;
        }

        $definition = $this->getDefinition($id);
        if ($definition === null) {
            return false;
        }

        if ($definition instanceof ObjectDefinition) {
            return $definition->isInstantiable();
        }

        return true;
    }

    /**
     * @param array|callable|string $definition
     */
    protected function setDefinition(string $name, $definition): void
    {
        // Clear existing entry if it exists
        if (array_key_exists($name, $this->resolvedEntries)) {
            unset($this->resolvedEntries[$name]);
        }
        $this->fetchedDefinitions = []; // Completely clear this local cache

        $this->definitionSource->addDefinition($name, $definition);
    }

    protected function getDefinition(string $name): ?DefinitionInterface
    {
        // Local cache that avoids fetching the same definition twice
        if (! array_key_exists($name, $this->fetchedDefinitions)) {
            $this->fetchedDefinitions[$name] = $this->definitionSource->getDefinition($name);
        }

        return $this->fetchedDefinitions[$name];
    }

    /**
     * Resolves a definition.
     */
    protected function resolveDefinition(DefinitionInterface $definition, array $parameters = [])
    {
        return $this->definitionResolver->resolve($definition, $parameters);
    }
}
