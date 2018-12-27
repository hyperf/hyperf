<?php

namespace Hyperflex\Di\Definition;


use Hyperflex\Di\Aop\AstCollector;

class FactoryDefinition implements DefinitionInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var callable
     */
    private $factory;

    /**
     * @var mixed[]
     */
    private $parameters = [];

    /**
     * @var array|null
     */
    private $ast;

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

    public function getAst(): array
    {
        if (null === $this->ast) {
            $this->ast = AstCollector::get($this->getFactory(), []);
        }
        return $this->ast;
    }

    public function __toString(): string
    {
        return 'Factory';
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