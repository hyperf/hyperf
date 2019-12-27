<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Definition;

interface DefinitionInterface
{
    /**
     * Definitions can be cast to string for debugging information.
     */
    public function __toString(): string;

    /**
     * Returns the name of the entry in the container.
     */
    public function getName(): string;

    /**
     * Set the name of the entry in the container.
     */
    public function setName(string $name);

    /**
     * Determine if the definition need to transfer to a proxy class.
     */
    public function isNeedProxy(): bool;
}
