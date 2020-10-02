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

use Hyperf\Di\ReflectionManager;

class ObjectDefinition implements DefinitionInterface
{
    /**
     * @var MethodInjection
     */
    protected $constructorInjection;

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
