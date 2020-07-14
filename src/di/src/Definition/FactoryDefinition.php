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
    /**
     * @var string
     */
    private $name;

    /**
     * @var callable|string
     */
    private $factory;

    /**
     * @var mixed[]
     */
    private $parameters = [];

    /**
     * @var bool
     */
    private $needProxy = false;

    public function __construct(string $name, $factory, array $parameters = [])
    {
        $this->name = $name;
        $this->factory = $factory;
        $this->parameters = $parameters;
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

    /**
     * @return callable|string
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return mixed[]
     */
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
