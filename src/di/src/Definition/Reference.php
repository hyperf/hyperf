<?php

namespace Hyperf\Di\Definition;


use Psr\Container\ContainerInterface;

class Reference implements DefinitionInterface, SelfResolvingDefinitionInterface
{

    /**
     * Entry name.
     *
     * @var string
     */
    private $name = '';

    /**
     * Name of the target entry.
     *
     * @var string
     */
    private $targetEntryName;

    /**
     * @var bool
     */
    private $needProxy = false;

    public function __construct(string $targetEntryName)
    {
        $this->targetEntryName = $targetEntryName;
    }

    /**
     * Returns the name of the entry in the container.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the entry in the container.
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getTargetEntryName(): string
    {
        return $this->targetEntryName;
    }

    public function resolve(ContainerInterface $container)
    {
        return $container->get($this->getTargetEntryName());
    }

    public function isResolvable(ContainerInterface $container): bool
    {
        return $container->has($this->getTargetEntryName());
    }

    /**
     * Definitions can be cast to string for debugging information.
     */
    public function __toString(): string
    {
        return sprintf('get(%s)', $this->targetEntryName);
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