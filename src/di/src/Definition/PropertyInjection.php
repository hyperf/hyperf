<?php

namespace Hyperf\Di\Definition;

/**
 * Describe an injection in a class property.
 */
class PropertyInjection
{
    /**
     * Property name.
     * @var string
     */
    private $propertyName;

    /**
     * Value that should be injected in the property.
     * @var mixed
     */
    private $value;

    /**
     * @param string $propertyName Property name
     * @param mixed $value Value that should be injected in the property
     */
    public function __construct(string $propertyName, $value)
    {
        $this->propertyName = $propertyName;
        $this->value = $value;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    /**
     * @return mixed Value that should be injected in the property
     */
    public function getValue()
    {
        return $this->value;
    }

}
