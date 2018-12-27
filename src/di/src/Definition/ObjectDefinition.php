<?php

namespace Hyperf\Di\Definition;

use Hyperf\Di\ReflectionManager;

class ObjectDefinition implements DefinitionInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $className;

    /**
     * @var MethodInjection
     */
    protected $constructorInjection;

    /**
     * @var PropertyInjection[]
     */
    protected $propertyInjections = [];

    protected $methodInjections = [];

    /**
     * @var bool
     */
    private $classExists = false;

    /**
     * @var bool
     */
    private $instantiable = false;

    /**
     * @var bool
     */
    private $needProxy = false;

    /**
     * @var string
     */
    private $proxyClassName;

    public function __construct(string $name, string $className = null)
    {
        $this->name = $name;
        $this->setClassName($className ?? $name);
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

    public function setClassName(string $className = null)
    {
        $this->className = $className;

        $this->updateCache();
    }

    public function getClassName(): string
    {
        if ($this->className !== null) {
            return $this->className;
        }

        return $this->name;
    }

    public function isClassExists(): bool
    {
        return $this->classExists;
    }

    public function isInstantiable(): bool
    {
        return $this->instantiable;
    }

    /**
     * @return \Hyperf\Di\Definition\MethodInjection|null
     */
    public function getConstructorInjection()
    {
        return $this->constructorInjection;
    }

    public function setConstructorInjection(MethodInjection $injection)
    {
        $this->constructorInjection = $injection;
    }

    public function completeConstructorInjection(MethodInjection $injection)
    {
        if ($this->constructorInjection !== null) {
            // Merge
            $this->constructorInjection->merge($injection);
        } else {
            // Set
            $this->constructorInjection = $injection;
        }
    }

    /**
     * @return PropertyInjection[]
     */
    public function getPropertyInjections(): array
    {
        return $this->propertyInjections;
    }

    public function addPropertyInjection(PropertyInjection $propertyInjection)
    {
        $this->propertyInjections[$propertyInjection->getPropertyName()] = $propertyInjection;
    }

    public function getProxyClassName(): string
    {
        return $this->proxyClassName;
    }

    public function setProxyClassName($proxyClassName): self
    {
        $this->proxyClassName = $proxyClassName;
        return $this;
    }

    private function updateCache()
    {
        $className = $this->getClassName();

        $this->classExists = class_exists($className) || interface_exists($className);

        if (! $this->classExists) {
            $this->instantiable = false;
            return;
        }

        $this->instantiable = ReflectionManager::reflectClass($className)->isInstantiable();
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

    public function __toString(): string
    {
        return sprintf('Object[%s]' . $this->getClassName());
    }

}