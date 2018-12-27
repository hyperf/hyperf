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
