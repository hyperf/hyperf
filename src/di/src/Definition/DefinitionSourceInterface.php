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
     * @return null|DefinitionInterface
     */
    public function getDefinition(string $name);

    /**
     * @return array definitions indexed by their name
     */
    public function getDefinitions(): array;

    /**
     * @param mixed $definition
     * @return $this
     */
    public function addDefinition(string $name, $definition);

    public function clearDefinitions(): void;
}
