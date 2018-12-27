<?php

namespace Hyperf\Di\Definition;


use Hyperf\Di\Exception\InvalidDefinitionException;

interface DefinitionSourceInterface
{

    /**
     * Returns the DI definition for the entry name.
     *
     * @throws InvalidDefinitionException An invalid definition was found.
     * @return array|null
     */
    public function getDefinition(string $name);

    /**
     * @return array Definitions indexed by their name.
     */
    public function getDefinitions(): array;

    /**
     * @return $this
     */
    public function addDefinition(string $name, array $definition);

    public function clearDefinitions(): void;

}