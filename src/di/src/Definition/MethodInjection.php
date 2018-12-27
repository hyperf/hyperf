<?php

namespace Hyperflex\Di\Definition;


class MethodInjection implements DefinitionInterface
{


    /**
     * @var string
     */
    private $methodName;

    /**
     * @var mixed[]
     */
    private $parameters = [];

    public function __construct(string $methodName, array $parameters = [])
    {
        $this->parameters = $parameters;
        $this->methodName = $methodName;
    }

    public function getName(): string
    {
        return '';
    }

    public function setName(string $name)
    {
        // The name does not matter for method injections, so do nothing.
    }

    /**
     * @return mixed[]
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    public function merge(self $definition)
    {
        // In case of conflicts, the current definition prevails.
        $this->parameters = $this->parameters + $definition->parameters;
    }

    public function __toString(): string
    {
        return sprintf('method(%s)', $this->methodName);
    }

    /**
     * Reset the target should be resolved.
     * If it is the FactoryDefinition, then the target means $factory property,
     * If it is the ObjectDefinition, then the target means $className property.
     *
     * @param mixed $value
     */
    public function setTarget($value)
    {
        $this->methodName = $value;
    }

    /**
     * Determine if the definition need to transfer to a proxy class.
     */
    public function isNeedProxy(): bool
    {
        // Method injection does not has proxy.
        return false;
    }
}