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

    public function __toString(): string
    {
        return sprintf('method(%s)', $this->methodName);
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
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function merge(self $definition)
    {
        // In case of conflicts, the current definition prevails.
        $this->parameters = $this->parameters + $definition->parameters;
    }

    /**
     * Reset the target should be resolved.
     * If it is the FactoryDefinition, then the target means $factory property,
     * If it is the ObjectDefinition, then the target means $className property.
     */
    public function setTarget(string $value)
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
