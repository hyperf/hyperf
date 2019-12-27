<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Definition;

use Hyperf\Di\ReflectionManager;

class ObjectDefinition implements DefinitionInterface
{
    /**
     * @var MethodInjection
     */
    protected $constructorInjection;

    /**
     * @var PropertyInjection[]
     */
    protected $propertyInjections = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @var null|string
     */
    private $className;

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

    public function __toString(): string
    {
        return sprintf('Object[%s]', $this->getClassName());
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

    public function setClassName(string $className = null): void
    {
        $this->className = $className;

        $this->updateStatusCache();
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
     * @return null|\Hyperf\Di\Definition\MethodInjection
     */
    public function getConstructorInjection()
    {
        return $this->constructorInjection;
    }

    public function setConstructorInjection(MethodInjection $injection): self
    {
        $this->constructorInjection = $injection;
        return $this;
    }

    public function completeConstructorInjection(MethodInjection $injection): void
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

    public function addPropertyInjection(PropertyInjection $propertyInjection): void
    {
        $this->propertyInjections[$propertyInjection->getPropertyName()] = $propertyInjection;
    }

    public function getProxyClassName(): string
    {
        return $this->proxyClassName;
    }

    public function setProxyClassName(string $proxyClassName): self
    {
        $this->proxyClassName = $proxyClassName;
        return $this;
    }

    /**
     * Determine if the definition need to transfer to a proxy class.
     */
    public function isNeedProxy(): bool
    {
        return $this->needProxy;
    }

    public function setNeedProxy(bool $needProxy): self
    {
        $this->needProxy = $needProxy;
        return $this;
    }

    private function updateStatusCache(): void
    {
        $className = $this->getClassName();

        $this->classExists = class_exists($className) || interface_exists($className);

        if (! $this->classExists) {
            $this->instantiable = false;
            return;
        }

        $this->instantiable = ReflectionManager::reflectClass($className)->isInstantiable();
    }
}
