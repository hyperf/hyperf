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
use RuntimeException;
use Throwable;

class Container implements HyperfContainerInterface
{
    /**
     * Map of entries that are already resolved.
     */
    private array $resolvedEntries;

    private array $loading = [];

    private array $loadingError = [];

    /**
     * Map of definitions that are already fetched (local cache).
     */
    private array $fetchedDefinitions = [];

    private Resolver\ResolverInterface $definitionResolver;

    /**
     * Container constructor.
     */
    public function __construct(private Definition\DefinitionSourceInterface $definitionSource)
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
     * Build an entry of the container by its name.
     * This method behave like get() except resolves the entry again every time.
     * For example if the entry is a class then a new instance will be created each time.
     * This method makes the container behave like a factory.
     *
     * @param string $name entry name or a class name
     * @param array $parameters Optional parameters to use to build the entry. Use this to force specific parameters
     *                          to specific values. Parameters not defined in this array will be resolved using
     *                          the container.
     * @throws NotFoundException no entry found for the given name
     * @throws InvalidArgumentException the name parameter must be of type string
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
     * Bind an arbitrary resolved entry to an identifier.
     * Useful for testing 'get'.
     * @param mixed $entry
     */
    public function set(string $name, $entry): void
    {
        $this->resolvedEntries[$name] = $entry;
    }

    /**
     * Unbind an arbitrary resolved entry.
     */
    public function unbind(string $name): void
    {
        if ($this->has($name)) {
            unset($this->resolvedEntries[$name]);
        }
    }

    /**
     * Bind an arbitrary definition to an identifier.
     * Useful for testing 'make'.
     *
     * @param array|callable|string $definition
     */
    public function define(string $name, $definition): void
    {
        $this->setDefinition($name, $definition);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id identifier of the entry to look for
     */
    public function get($id)
    {
        // If the entry is already resolved we return it
        if (isset($this->resolvedEntries[$id]) || array_key_exists($id, $this->resolvedEntries)) {
            return $this->resolvedEntries[$id];
        }

        if (! isset($this->loading[$id])) {
            $this->loading[$id] = true;
            unset($this->loadingError[$id]);
            try {
                return $this->resolvedEntries[$id] = $this->make($id);
            } catch (Throwable $throwable) {
                $this->loadingError[$id] = (string) $throwable;
                throw new $throwable();
            } finally {
                unset($this->loading[$id]);
            }
        }
        return $this->waitLoading($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * `has($name)` returning true does not mean that `get($name)` will not throw an exception.
     * It does however mean that `get($name)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param mixed|string $id identifier of the entry to look for
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
     * @deprecated
     */
    public function getDefinitionSource(): Definition\DefinitionSourceInterface
    {
        return $this->definitionSource;
    }

    private function waitLoading($id)
    {
        $startTime = time();
        while (true) {
            if (isset($this->resolvedEntries[$id]) || array_key_exists($id, $this->resolvedEntries)) {
                return $this->resolvedEntries[$id];
            }
            if (isset($this->loadingError[$id])) {
                throw new RuntimeException($this->loadingError[$id]);
            }
            if (time() - $startTime > 5) {
                throw new RuntimeException("The get entry or class timed out for 5 seconds for {$id}");
            }
            usleep(1000);
        }
    }

    /**
     * @param array|callable|string $definition
     */
    private function setDefinition(string $name, $definition): void
    {
        // Clear existing entry if it exists
        if (array_key_exists($name, $this->resolvedEntries)) {
            unset($this->resolvedEntries[$name]);
        }
        $this->fetchedDefinitions = []; // Completely clear this local cache

        $this->definitionSource->addDefinition($name, $definition);
    }

    private function getDefinition(string $name): ?DefinitionInterface
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
    private function resolveDefinition(DefinitionInterface $definition, array $parameters = [])
    {
        return $this->definitionResolver->resolve($definition, $parameters);
    }
}
