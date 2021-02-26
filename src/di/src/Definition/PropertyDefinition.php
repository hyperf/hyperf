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
     * Property name.
     *
     * @var string
     */
    private $propertyName;

    /**
     * Value that should be injected in the property.
     *
     * @var mixed
     */
    private $value;

    /**
     * Use for injecting in properties of parent classes: the class name
     * must be the name of the parent class because private properties
     * can be attached to the parent classes, not the one we are resolving.
     * @var null|string
     */
    private $className;

    /**
     * PropertyDefinition constructor.
     * @param mixed $value
     */
    public function __construct(string $propertyName, $value, ?string $className = null)
    {
        $this->propertyName = $propertyName;
        $this->value = $value;
        $this->className = $className;
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
