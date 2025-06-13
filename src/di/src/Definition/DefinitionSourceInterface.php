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

use Hyperf\Di\Exception\InvalidDefinitionException;

interface DefinitionSourceInterface
{
    /**
     * Returns the DI definition for the entry name.
     *
     * @throws InvalidDefinitionException an invalid definition was found
     */
    public function getDefinition(string $name): ?DefinitionInterface;

    /**
     * @return array definitions indexed by their name
     */
    public function getDefinitions(): array;

    /**
     * @param array|callable|string $definition
     */
    public function addDefinition(string $name, $definition): static;

    public function clearDefinitions(): void;
}
