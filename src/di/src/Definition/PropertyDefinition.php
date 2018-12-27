<?php

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
     * @var string|null
     */
    private $className;

    /**
     * PropertyDefinition constructor.
     *
     * @param string $propertyName
     * @param mixed $value
     * @param string|null $className
     */
    public function __construct(string $propertyName, $value, ?string $className = null)
    {
        $this->propertyName = $propertyName;
        $this->value = $value;
        $this->className = $className;
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

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
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

    /**
     * Definitions can be cast to string for debugging information.
     */
    public function __toString(): string
    {
        return 'Property';
    }
}