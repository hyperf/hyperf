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

class PropertyDefinition implements DefinitionInterface
{
    /**
     * @param string $propertyName property name
     * @param mixed $value value that should be injected in the property
     * @param null|string $className use for injecting in properties of parent classes: the class name
     *                               must be the name of the parent class because private properties
     *                               can be attached to the parent classes, not the one we are resolving
     */
    public function __construct(private string $propertyName, private $value, private ?string $className = null)
    {
    }

    /**
     * Definitions can be cast to string for debugging information.
     */
    public function __toString(): string
    {
        return 'Property';
    }

    /**
     * Returns the name of the entry in the container.
     */
    public function getName(): string
    {
        return $this->propertyName;
    }

    /**
     * Set the name of the entry in the container.
     */
    public function setName(string $name)
    {
        $this->propertyName = $name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * Determine if the definition need to transfer to a proxy class.
     */
    public function isNeedProxy(): bool
    {
        return false;
    }
}
