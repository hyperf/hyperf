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

class FactoryDefinition implements DefinitionInterface
{
    private bool $needProxy = false;

    /**
     * @param callable|string $factory
     */
    public function __construct(private string $name, private mixed $factory, private array $parameters = [])
    {
    }

    public function __toString(): string
    {
        return 'Factory';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getFactory(): callable|string
    {
        return $this->factory;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Determine if the definition need to transfer to a proxy class.
     */
    public function isNeedProxy(): bool
    {
        return $this->needProxy;
    }

    public function setNeedProxy($needProxy): self
    {
        $this->needProxy = $needProxy;
        return $this;
    }
}
